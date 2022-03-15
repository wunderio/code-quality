<?php

namespace Wunderio\GrumPHP\PhpCodingStandards\Sniffs\Commenting;

use Drupal\Sniffs\Commenting\InlineCommentSniff;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Customized Drupal\Sniffs\Commenting\InlineCommentSniff.
 *
 * Add support for Psalm docblocks.
 */
class WunderInlineCommentSniff extends InlineCommentSniff implements Sniff {

  /**
   * {@inheritdoc}
   */
  public function process(File $phpcsFile, $stackPtr) {
    $tokens = $phpcsFile->getTokens();

    // If this is a function/class/interface doc block comment, skip it.
    // We are only interested in inline doc block comments, which are
    // not allowed.
    if ($tokens[$stackPtr]['code'] === T_DOC_COMMENT_OPEN_TAG) {
      $nextToken = $phpcsFile->findNext(
        Tokens::$emptyTokens,
        ($stackPtr + 1),
        NULL,
        TRUE
      );

      $ignore = [
        T_CLASS,
        T_INTERFACE,
        T_TRAIT,
        T_FUNCTION,
        T_CLOSURE,
        T_PUBLIC,
        T_PRIVATE,
        T_PROTECTED,
        T_FINAL,
        T_STATIC,
        T_ABSTRACT,
        T_CONST,
        T_PROPERTY,
        T_INCLUDE,
        T_INCLUDE_ONCE,
        T_REQUIRE,
        T_REQUIRE_ONCE,
        T_VAR,
      ];

      // Also ignore all doc blocks defined in the outer scope (no scope
      // conditions are set).
      if (in_array($tokens[$nextToken]['code'], $ignore, TRUE) === TRUE
        || empty($tokens[$stackPtr]['conditions']) === TRUE
      ) {
        // phpcs:ignore Drupal.Commenting.FunctionComment.InvalidReturnNotVoid
        return;
      }

      if ($phpcsFile->tokenizerType === 'JS') {
        // We allow block comments if a function or object
        // is being assigned to a variable.
        $ignore    = Tokens::$emptyTokens;
        $ignore[]  = T_EQUAL;
        $ignore[]  = T_STRING;
        $ignore[]  = T_OBJECT_OPERATOR;
        $nextToken = $phpcsFile->findNext($ignore, ($nextToken + 1), NULL, TRUE);
        if ($tokens[$nextToken]['code'] === T_FUNCTION
          || $tokens[$nextToken]['code'] === T_CLOSURE
          || $tokens[$nextToken]['code'] === T_OBJECT
          || $tokens[$nextToken]['code'] === T_PROTOTYPE
        ) {
          // phpcs:ignore Drupal.Commenting.FunctionComment.InvalidReturnNotVoid
          return;
        }
      }

      $prevToken = $phpcsFile->findPrevious(
        Tokens::$emptyTokens,
        ($stackPtr - 1),
        NULL,
        TRUE
      );

      if ($tokens[$prevToken]['code'] === T_OPEN_TAG) {
        // phpcs:ignore Drupal.Commenting.FunctionComment.InvalidReturnNotVoid
        return;
      }

      // Inline doc blocks are allowed in JSDoc.
      if ($tokens[$stackPtr]['content'] === '/**' && $phpcsFile->tokenizerType !== 'JS') {
        // The only exception to inline doc blocks is the /** @var */
        // declaration. Allow that in any form.
        $varTag = $phpcsFile->findNext([T_DOC_COMMENT_TAG], ($stackPtr + 1), $tokens[$stackPtr]['comment_closer'], FALSE, '@var');
        if ($varTag === FALSE) {
          $error = 'Inline doc block comments are not allowed; use "/* Comment */" or "// Comment" instead';
          $phpcsFile->addError($error, $stackPtr, 'DocBlock');
        }
      }
    }

    if ($tokens[$stackPtr]['content'][0] === '#') {
      $error = 'Perl-style comments are not allowed; use "// Comment" instead';
      $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'WrongStyle');
      if ($fix === TRUE) {
        $comment = ltrim($tokens[$stackPtr]['content'], "# \t");
        $phpcsFile->fixer->replaceToken($stackPtr, "// $comment");
      }
    }

    // We don't want end of block comments. Check if the last token before the
    // comment is a closing curly brace.
    $previousContent = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), NULL, TRUE);
    if ($tokens[$previousContent]['line'] === $tokens[$stackPtr]['line']) {
      if ($tokens[$previousContent]['code'] === T_CLOSE_CURLY_BRACKET) {
        // phpcs:ignore Drupal.Commenting.FunctionComment.InvalidReturnNotVoid
        return;
      }

      // Special case for JS files.
      if ($tokens[$previousContent]['code'] === T_COMMA
        || $tokens[$previousContent]['code'] === T_SEMICOLON
      ) {
        $lastContent = $phpcsFile->findPrevious(T_WHITESPACE, ($previousContent - 1), NULL, TRUE);
        if ($tokens[$lastContent]['code'] === T_CLOSE_CURLY_BRACKET) {
          // phpcs:ignore Drupal.Commenting.FunctionComment.InvalidReturnNotVoid
          return;
        }
      }
    }

    // Only want inline comments.
    if (substr($tokens[$stackPtr]['content'], 0, 2) !== '//') {
      // phpcs:ignore Drupal.Commenting.FunctionComment.InvalidReturnNotVoid
      return;
    }

    // Ignore code example lines.
    if ($this->isInCodeExample($phpcsFile, $stackPtr) === TRUE) {
      // phpcs:ignore Drupal.Commenting.FunctionComment.InvalidReturnNotVoid
      return;
    }

    $commentTokens = [$stackPtr];

    $nextComment = $stackPtr;
    $lastComment = $stackPtr;
    while (($nextComment = $phpcsFile->findNext(T_COMMENT, ($nextComment + 1), NULL, FALSE)) !== FALSE) {
      if ($tokens[$nextComment]['line'] !== ($tokens[$lastComment]['line'] + 1)) {
        break;
      }

      // Only want inline comments.
      if (substr($tokens[$nextComment]['content'], 0, 2) !== '//') {
        break;
      }

      // There is a comment on the very next line. If there is
      // no code between the comments, they are part of the same
      // comment block.
      $prevNonWhitespace = $phpcsFile->findPrevious(T_WHITESPACE, ($nextComment - 1), $lastComment, TRUE);
      if ($prevNonWhitespace !== $lastComment) {
        break;
      }

      // A comment starting with "@" means a new comment section.
      if (preg_match('|^//[\s]*@|', $tokens[$nextComment]['content']) === 1) {
        break;
      }

      $commentTokens[] = $nextComment;
      $lastComment     = $nextComment;
    }

    $commentText      = '';
    $lastCommentToken = $stackPtr;
    foreach ($commentTokens as $lastCommentToken) {
      $comment = rtrim($tokens[$lastCommentToken]['content']);

      if (trim(substr($comment, 2)) === '') {
        continue;
      }

      $spaceCount = 0;
      $tabFound   = FALSE;

      $commentLength = strlen($comment);
      for ($i = 2; $i < $commentLength; $i++) {
        if ($comment[$i] === "\t") {
          $tabFound = TRUE;
          break;
        }

        if ($comment[$i] !== ' ') {
          break;
        }

        $spaceCount++;
      }

      $fix = FALSE;
      if ($tabFound === TRUE) {
        $error = 'Tab found before comment text; expected "// %s" but found "%s"';
        $data  = [
          ltrim(substr($comment, 2)),
          $comment,
        ];
        $fix   = $phpcsFile->addFixableError($error, $lastCommentToken, 'TabBefore', $data);
      }
      elseif ($spaceCount === 0) {
        $error = 'No space found before comment text; expected "// %s" but found "%s"';
        $data  = [
          substr($comment, 2),
          $comment,
        ];
        $fix   = $phpcsFile->addFixableError($error, $lastCommentToken, 'NoSpaceBefore', $data);
      }//end if

      if ($fix === TRUE) {
        $newComment = '// ' . ltrim($tokens[$lastCommentToken]['content'], "/\t ");
        $phpcsFile->fixer->replaceToken($lastCommentToken, $newComment);
      }

      if ($spaceCount > 1) {
        // Check if there is a comment on the previous line that justifies the
        // indentation.
        $prevComment = $phpcsFile->findPrevious([T_COMMENT], ($lastCommentToken - 1), NULL, FALSE);
        if (($prevComment !== FALSE) && (($tokens[$prevComment]['line']) === ($tokens[$lastCommentToken]['line'] - 1))) {
          $prevCommentText = rtrim($tokens[$prevComment]['content']);
          $prevSpaceCount  = 0;
          for ($i = 2; $i < strlen($prevCommentText); $i++) {
            if ($prevCommentText[$i] !== ' ') {
              break;
            }

            $prevSpaceCount++;
          }

          if ($spaceCount > $prevSpaceCount && $prevSpaceCount > 0) {
            // A previous comment could be a list item or @todo.
            $indentationStarters = [
              '-',
              '@todo',
            ];
            $words = preg_split('/\s+/', $prevCommentText);
            $numberedList = (bool) preg_match('/^[0-9]+\./', $words[1]);
            if (in_array($words[1], $indentationStarters) === TRUE) {
              if ($spaceCount !== ($prevSpaceCount + 2)) {
                $error = 'Comment indentation error after %s element, expected %s spaces';
                $fix = $phpcsFile->addFixableError($error, $lastCommentToken, 'SpacingBefore', [
                  $words[1],
                  ($prevSpaceCount + 2),
                ]);
                if ($fix === TRUE) {
                  $newComment = '//' . str_repeat(' ', ($prevSpaceCount + 2)) . ltrim($tokens[$lastCommentToken]['content'], "/\t ");
                  $phpcsFile->fixer->replaceToken($lastCommentToken, $newComment);
                }
              }
            }
            elseif ($numberedList === TRUE) {
              $expectedSpaceCount = ($prevSpaceCount + strlen($words[1]) + 1);
              if ($spaceCount !== $expectedSpaceCount) {
                $error = 'Comment indentation error, expected %s spaces';
                $fix   = $phpcsFile->addFixableError($error, $lastCommentToken, 'SpacingBefore', [$expectedSpaceCount]);
                if ($fix === TRUE) {
                  $newComment = '//' . str_repeat(' ', $expectedSpaceCount) . ltrim($tokens[$lastCommentToken]['content'], "/\t ");
                  $phpcsFile->fixer->replaceToken($lastCommentToken, $newComment);
                }
              }
            }
            else {
              $error = 'Comment indentation error, expected only %s spaces';
              $phpcsFile->addError($error, $lastCommentToken, 'SpacingBefore', [$prevSpaceCount]);
            }
          }
        }
        else {
          $error = '%s spaces found before inline comment; expected "// %s" but found "%s"';
          $data  = [
            $spaceCount,
            substr($comment, (2 + $spaceCount)),
            $comment,
          ];
          $fix   = $phpcsFile->addFixableError($error, $lastCommentToken, 'SpacingBefore', $data);
          if ($fix === TRUE) {
            $phpcsFile->fixer->replaceToken($lastCommentToken, '// ' . substr($comment, (2 + $spaceCount)) . $phpcsFile->eolChar);
          }
        }
      }

      $commentText .= trim(substr($tokens[$lastCommentToken]['content'], 2));
    }

    if ($commentText === '') {
      $error = 'Blank comments are not allowed';
      $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'Empty');
      if ($fix === TRUE) {
        $phpcsFile->fixer->replaceToken($stackPtr, '');
      }

      return ($lastCommentToken + 1);
    }

    $words = preg_split('/\s+/', $commentText);
    if (preg_match('/^\p{Ll}/u', $commentText) === 1) {
      // Allow special lower cased words that contain non-alpha characters
      // (function references, machine names with underscores etc.).
      $matches = [];
      preg_match('/[a-z]+/', $words[0], $matches);
      if (isset($matches[0]) === TRUE && $matches[0] === $words[0]) {
        $error = 'Inline comments must start with a capital letter';
        $fix   = $phpcsFile->addFixableError($error, $stackPtr, 'NotCapital');
        if ($fix === TRUE) {
          // phpcs:ignore PHPCS_SecurityAudit.BadFunctions.PregReplace.PregReplaceDyn
          $newComment = preg_replace("/$words[0]/", ucfirst($words[0]), $tokens[$stackPtr]['content'], 1);
          $phpcsFile->fixer->replaceToken($stackPtr, $newComment);
        }
      }
    }

    // Only check the end of comment character if the start of the comment
    // is a letter, indicating that the comment is just standard text.
    if (preg_match('/^\p{L}/u', $commentText) === 1) {
      $commentCloser   = $commentText[(strlen($commentText) - 1)];
      $acceptedClosers = [
        'full-stops'       => '.',
        'exclamation marks'    => '!',
        'question marks'     => '?',
        'colons'         => ':',
        'or closing parentheses' => ')',
      ];

      // Allow special last words like URLs or function references
      // without punctuation.
      $lastWord = $words[(count($words) - 1)];
      $matches  = [];
      preg_match('/https?:\/\/.+/', $lastWord, $matches);
      $isUrl = isset($matches[0]) === TRUE;
      preg_match('/[$a-zA-Z_]+\([$a-zA-Z_]*\)/', $lastWord, $matches);
      $isFunction = isset($matches[0]) === TRUE;

      // Also allow closing tags like @endlink or @endcode.
      $isEndTag = $lastWord[0] === '@';

      if (in_array($commentCloser, $acceptedClosers, TRUE) === FALSE
        && $isUrl === FALSE && $isFunction === FALSE && $isEndTag === FALSE
      ) {
        $error = 'Inline comments must end in %s';
        $ender = '';
        foreach ($acceptedClosers as $closerName => $symbol) {
          $ender .= ' ' . $closerName . ',';
        }

        $ender = trim($ender, ' ,');
        $data  = [$ender];
        $fix   = $phpcsFile->addFixableError($error, $lastCommentToken, 'InvalidEndChar', $data);
        if ($fix === TRUE) {
          $newContent = preg_replace('/(\s+)$/', '.$1', $tokens[$lastCommentToken]['content']);
          $phpcsFile->fixer->replaceToken($lastCommentToken, $newContent);
        }
      }
    }

    // Finally, the line below the last comment cannot be empty if this inline
    // comment is on a line by itself.
    if ($tokens[$previousContent]['line'] < $tokens[$stackPtr]['line']) {
      $next = $phpcsFile->findNext(T_WHITESPACE, ($lastCommentToken + 1), NULL, TRUE);
      if ($next === FALSE) {
        // Ignore if the comment is the last non-whitespace token in a file.
        return ($lastCommentToken + 1);
      }

      if ($tokens[$next]['code'] === T_DOC_COMMENT_OPEN_TAG) {
        // If this inline comment is followed by a docblock,
        // ignore spacing as docblock/function etc spacing rules
        // are likely to conflict with our rules.
        return ($lastCommentToken + 1);
      }

      $errorCode = 'SpacingAfter';

      if (isset($tokens[$stackPtr]['conditions']) === TRUE) {
        $conditions = $tokens[$stackPtr]['conditions'];
        $type = end($conditions);
        $conditionPtr = key($conditions);

        if (($type === T_FUNCTION || $type === T_CLOSURE)
          && $tokens[$conditionPtr]['scope_closer'] === $next
        ) {
          $errorCode = 'SpacingAfterAtFunctionEnd';
        }
      }

      for ($i = ($lastCommentToken + 1); $i < $phpcsFile->numTokens; $i++) {
        if ($tokens[$i]['line'] === ($tokens[$lastCommentToken]['line'] + 1)) {
          if ($tokens[$i]['code'] !== T_WHITESPACE) {
            return ($lastCommentToken + 1);
          }
        }
        elseif ($tokens[$i]['line'] > ($tokens[$lastCommentToken]['line'] + 1)) {
          break;
        }
      }

      $error = 'There must be no blank line following an inline comment';
      $fix   = $phpcsFile->addFixableWarning($error, $lastCommentToken, $errorCode);
      if ($fix === TRUE) {
        $phpcsFile->fixer->beginChangeset();
        for ($i = ($lastCommentToken + 1); $i < $next; $i++) {
          if ($tokens[$i]['line'] === $tokens[$next]['line']) {
            break;
          }

          $phpcsFile->fixer->replaceToken($i, '');
        }

        $phpcsFile->fixer->endChangeset();
      }
    }

    return ($lastCommentToken + 1);

  }

}
