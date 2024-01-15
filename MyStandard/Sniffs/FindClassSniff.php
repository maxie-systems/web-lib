<?php
/**
 * This sniff ... .
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
use PHP_CodeSniffer\Util\Tokens;

class FindClassSniff implements Sniff
{
    /**
     * @return array(int)
     */
    public function register(): array
    {
        return [
            T_CLASS,
            T_INTERFACE,
  //          T_ENUM,//php ./vendor/bin/phpcs --standard=MyStandard MyStandard/Tests/
            //T_TRAIT,//php ./vendor/bin/phpcs --standard=PSR12 --exclude=src/HTTP/ src/
            //T_FUNCTION,//https://github.com/squizlabs/PHP_CodeSniffer/wiki/Annotated-Ruleset
        ];
    }

    /**
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The current file being checked.
     * @param int                         $stackPtr  The position of the current token in the
     *                                               stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$stackPtr];
        $entity = strtolower($token['content']);
        $ns = $this->findNamespace($phpcsFile, $stackPtr);
        if ($ns) {
            //var_dump($ns);
        } else {
            $e_msg_no_ns = 'Each %s must be in a namespace of at least one level (a top-level vendor name)';
            $phpcsFile->addError($e_msg_no_ns, $stackPtr, 'MissingNamespace', [$entity]);
        }
        $fqn = $ns;
        $fqn[] = $phpcsFile->getDeclarationName($stackPtr);
        $fqn = implode('\\', $fqn);
        var_dump($phpcsFile->getFilename());
//        var_dump($fqn);
        $start = $phpcsFile->findStartOfStatement($stackPtr);
        var_dump($phpcsFile->getTokensAsString($start, $phpcsFile->findNext([T_OPEN_CURLY_BRACKET], $start) - $start));
//        var_dump($phpcsFile->getClassProperties($stackPtr));
//        var_dump($phpcsFile->getMethodProperties($stackPtr));
    }

    protected function findNamespace(File $phpcsFile, $stackPtr): array
    {
        $start = $phpcsFile->findNext([T_NAMESPACE], 0);
        if (false === $start) {
            return [];
        }
        $end = $phpcsFile->findEndOfStatement($start);
        $tokens = $phpcsFile->getTokens();
        return match ($tokens[$end]['code']) {
            T_SEMICOLON => $this->extractNS($phpcsFile, $start, $end),
            T_CLOSE_CURLY_BRACKET => $this->extractCurlyBracketsNS($phpcsFile, $stackPtr, $start, $end)
        };
    }

    private function extractNS(File $phpcsFile, int $start, int $end): array
    {
        $start = $phpcsFile->findNext(Tokens::$emptyTokens, $start + 1, null, true);
        $tokens = $phpcsFile->getTokens();
        $ns = [];
        if (T_NS_SEPARATOR === $tokens[$start]['code']) {
            return $ns;
        }
        for ($i = $start; $i < $end; ++$i) {
            if (T_STRING === $tokens[$i]['code']) {
                $ns[] = $tokens[$i]['content'];
            } elseif (T_NS_SEPARATOR !== $tokens[$i]['code']) {
                break;
            }
        }
        return $ns;
    }

    private function extractCurlyBracketsNS(File $phpcsFile, int $stackPtr, int $start, int $end): array
    {
        if ($stackPtr > $end) {
            $s = $phpcsFile->findNext([T_NAMESPACE], $end + 1);
            return false === $s ? [] : $this->extractCurlyBracketsNS(
                        $phpcsFile, $stackPtr, $s, $phpcsFile->findEndOfStatement($s)
                    );
        }
        ++$start;
        return $this->extractNS($phpcsFile, $start, $phpcsFile->findNext([T_OPEN_CURLY_BRACKET], $start));
    }
}
