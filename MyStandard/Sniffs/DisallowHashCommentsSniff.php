<?php
/**
 * This sniff prohibits the use of Perl style hash comments.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Max Antipin <max.v.antipin@gmail.com>
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence !!!
 * @link      http://pear.php.net/package/PHP_CodeSniffer !!!
 */

namespace PHP_CodeSniffer\Standards\MyStandard\Sniffs;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

class DisallowHashCommentsSniff implements Sniff
{
    /**
     * @return array(int)
     */
    public function register(): array
    {
        return [T_COMMENT];
    }

    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The current file being checked.
     * @param int                         $stackPtr  The position of the current token in the
     *                                               stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        if ($tokens[$stackPtr]['content'][0] === '#') {
            $error = 'Hash comments are prohibited; found %s';
            $data  = array(trim($tokens[$stackPtr]['content']));
            //$phpcsFile->addError($error, $stackPtr, 'Found', $data);
        }

    }
}
