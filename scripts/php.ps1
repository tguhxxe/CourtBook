$portablePhp = Join-Path $env:LOCALAPPDATA 'CourtBook\php84\php.exe'
if (-not (Test-Path -LiteralPath $portablePhp)) { throw 'PHP portabel belum ada. Gunakan PHP 8.4+ dengan pdo_sqlite atau ikuti README.' }
& $portablePhp @args
exit $LASTEXITCODE
