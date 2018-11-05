<?php
/**
 * Created by PhpStorm.
 * User: junade
 * Date: 14/05/2017
 * Time: 05:03
 */

namespace IcyApril\WhatsChanged;


class Git implements VCS
{
    private $binary = '/usr/bin/git';

    public function __construct($binary = "")
    {
        if (!empty($binary)) {
            $this->binary = $binary;
        }

        if ($this->isOSWindows() === true) {
            throw new GitException("No Windows support");
        }

        if ($this->gitExists() !== true) {
            throw new GitException("Git doesn't appear to exist in: " . $this->binary);
        }

        if ($this->isProjectGit() !== true) {
            throw new GitException("No Git project appears to be in: " . getcwd());
        }
    }

    private function isOSWindows(): bool
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return true;
        }

        return false;
    }

    public function gitExists(): bool
    {
        $returnVar = intval(trim(shell_exec($this->binary . " --help &> /dev/null; echo $?")));

        if ($returnVar === 0) {
            return true;
        }

        return false;
    }

    private function isProjectGit(): bool
    {
        $isProjectGit = trim(shell_exec("git rev-parse --is-inside-work-tree"));

        if ($isProjectGit === "true") {
            return true;
        }

        return false;
    }

    public function getChangedFiles(): array
    {
        $arguments = $_SERVER['argv'];
        
        $from = isset($arguments[1]) ? $arguments[1] : 'HEAD^';
        $to = isset($arguments[2]) ? $arguments[2] : 'HEAD';
        
        $changes = $this->execute("git diff --name-only");
        $changes .= PHP_EOL;
        $changes .= $this->execute("git diff --name-only $from $to");
        
        $changes = trim($changes);
        $files = explode(PHP_EOL, $changes);
        return $files;
    }

    private function countCommits(): int
    {
        return intval(trim($this->execute("git shortlog | grep -E '^[ ]+\\w+' | wc -l")));
    }

    private function execute(string $command): string
    {
        return trim(shell_exec($command));
    }

}
