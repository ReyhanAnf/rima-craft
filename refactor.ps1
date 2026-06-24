$files = Get-ChildItem -Path "d:\01_WORK\_Active\PKM\Rima_Craft\rima-craft\resources\views" -Filter *.blade.php -Recurse

foreach ($file in $files) {
    $content = Get-Content $file.FullName -Raw

    $content = [regex]::Replace($content, "rounded-3xl", "rounded-md")
    $content = [regex]::Replace($content, "rounded-2xl", "rounded-md")
    $content = [regex]::Replace($content, "rounded-xl", "rounded-md")

    $content = [regex]::Replace($content, "\bp-8\b", "p-4")
    $content = [regex]::Replace($content, "\bp-6\b", "p-4")
    $content = [regex]::Replace($content, "\bp-5\b", "p-4")
    $content = [regex]::Replace($content, "\bpx-6\b", "px-4")
    $content = [regex]::Replace($content, "\bpx-5\b", "px-4")
    $content = [regex]::Replace($content, "\bpy-4\b", "py-2.5")

    $content = [regex]::Replace($content, "\bgap-6\b", "gap-4")
    $content = [regex]::Replace($content, "\bgap-5\b", "gap-4")
    
    $content = [regex]::Replace($content, "(?s)<div class=`"absolute[^>]*?blur-2xl[^>]*?`"></div>", "")
    $content = [regex]::Replace($content, "(?s)<div class=`"absolute[^>]*?blur-3xl[^>]*?`"></div>", "")

    if ($file.Name -eq "app.blade.php") {
        $content = [regex]::Replace($content, "\bw-72\b", "w-64")
        $content = [regex]::Replace($content, "bg-radial-gradient", "bg-gray-50 dark:bg-gray-950")
    }

    Set-Content -Path $file.FullName -Value $content -Encoding UTF8
}

Write-Host "Refactoring Selesai"
