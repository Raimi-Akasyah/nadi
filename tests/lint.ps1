# Runs php -l on every PHP file in the project (outside .git and .agents).
$ErrorActionPreference = 'Stop'
$php = 'C:\xampp 8.2\php\php.exe'
$root = Split-Path -Parent $PSScriptRoot
$failed = 0
$count = 0
Get-ChildItem -Path $root -Recurse -File -Filter '*.php' |
    Where-Object { $_.FullName -notmatch '[\\/]\.(git|agents)[\\/]' } |
    ForEach-Object {
        $count++
        $out = & $php -l $_.FullName 2>&1
        if ($LASTEXITCODE -ne 0) {
            $failed++
            Write-Output "FAIL $($_.FullName)"
            Write-Output $out
        }
    }
Write-Output "Linted $count files, $failed failed."
if ($failed -gt 0) { exit 1 }
exit 0
