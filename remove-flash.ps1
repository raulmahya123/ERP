$viewsPath = "C:\Users\raulm\Downloads\ERP\resources\views"
$files = Get-ChildItem -Recurse -Filter "*.blade.php" $viewsPath | Where-Object { 
    $_.FullName -notlike "*\components\flash-messages.blade.php" -and 
    $_.FullName -notlike "*\layouts\app.blade.php"
}

$modifiedFiles = @()

foreach ($file in $files) {
    $content = [System.IO.File]::ReadAllText($file.FullName, [System.Text.Encoding]::UTF8)
    $original = $content
    
    # Remove @if(session('success'))...@endif blocks that don't contain Swal.fire
    # Also handles @if(session('error')) and @if(session('status'))
    
    # Pattern: @if (session('xxx'))...[any content]...@endif
    # Works for both single-line and multi-line blocks
    $pattern = '(?ms)(^|\n)[ \t]*@if\s*\(\s*session\s*\(\s*''(?:success|error|status)''\s*\)\s*\)[\s\S]*?@endif[ \t]*(\r?\n|$)'
    
    $content = [regex]::Replace($content, $pattern, {
        param($match)
        $block = $match.Value
        # Skip blocks that contain Swal.fire() calls
        if ($block -match 'Swal\.fire') {
            return $match.Value
        }
        # Remove the block - keep the leading newline but remove the rest
        $leadingNewline = $matches[1]
        $trailingNewline = $matches[2]
        # Return empty string (remove the block entirely)
        return ''
    })
    
    if ($content -ne $original) {
        # Clean up multiple blank lines (3+ newlines -> 2 newlines)
        $content = $content -replace '\r?\n\s*\r?\n\s*\r?\n', "`r`n`r`n"
        $content = $content -replace '\r?\n\s*\r?\n\s*\r?\n', "`r`n`r`n"
        
        [System.IO.File]::WriteAllText($file.FullName, $content, [System.Text.Encoding]::UTF8)
        $modifiedFiles += $file.FullName
        Write-Host "Modified: $($file.FullName.Substring($viewsPath.Length))"
    }
}

Write-Host ""
Write-Host "=== DONE ==="
Write-Host "Modified $($modifiedFiles.Count) files"
$modifiedFiles | ForEach-Object { Write-Host $_.Substring($viewsPath.Length) }
