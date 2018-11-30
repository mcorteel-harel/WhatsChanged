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
    private $binary = 'git';

    public function __construct($binary = "")
    {
        if (!empty($binary)) {
            $this->binary = $binary;
        }

        if ($this->isOSWindows() === true) {
            throw new GitException("No Windows support");
        }

        if ($this->gitExists() !== true) {
            throw new GitException($this->binary . " doesn't appear to exist in: " . $this->binary);
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
        return !empty('which ' . $this->binary);
    }

    private function isProjectGit(): bool
    {
        $isProjectGit = trim(shell_exec($this->binary . " rev-parse --is-inside-work-tree"));

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
        
        $changes = $this->execute($this->binary . " diff --name-only");
        $changes .= PHP_EOL;
        $changes .= $this->execute($this->binary . " diff --name-only $from $to");
        
        $changes = trim($changes);
        $files = explode(PHP_EOL, $changes);
        return $files;
    }

    private function countCommits(): int
    {
        return intval(trim($this->execute($this->binary . " shortlog | grep -E '^[ ]+\\w+' | wc -l")));
    }

    private function execute(string $command): string
    {
        return trim(shell_exec($command));
    }

}
