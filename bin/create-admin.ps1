param(
    [Parameter(Mandatory = $true)]
    [string]$PhpPath
)

$ErrorActionPreference = 'Stop'

function Convert-SecureStringToPlainText([Security.SecureString]$Value) {
    $pointer = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($Value)
    try {
        return [Runtime.InteropServices.Marshal]::PtrToStringBSTR($pointer)
    }
    finally {
        [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($pointer)
    }
}

try {
    $name = (Read-Host 'Nom complet').Trim()
    $email = (Read-Host 'Adresse e-mail').Trim().ToLowerInvariant()
    $passwordSecure = Read-Host 'Nouveau mot de passe (12 caractères minimum)' -AsSecureString
    $confirmationSecure = Read-Host 'Confirmer le mot de passe' -AsSecureString
    $password = Convert-SecureStringToPlainText $passwordSecure
    $confirmation = Convert-SecureStringToPlainText $confirmationSecure

    if ($password -cne $confirmation) {
        throw 'Les deux mots de passe ne correspondent pas.'
    }

    $env:IUT_ADMIN_NAME = $name
    $env:IUT_ADMIN_EMAIL = $email
    $env:IUT_ADMIN_PASSWORD = $password
    $phpScript = Join-Path $PSScriptRoot 'create-admin.php'
    & $PhpPath $phpScript --prepared
    if ($LASTEXITCODE -ne 0) {
        exit $LASTEXITCODE
    }
}
catch {
    [Console]::Error.WriteLine($_.Exception.Message)
    exit 1
}
finally {
    $env:IUT_ADMIN_NAME = $null
    $env:IUT_ADMIN_EMAIL = $null
    $env:IUT_ADMIN_PASSWORD = $null
    $password = $null
    $confirmation = $null
    $passwordSecure = $null
    $confirmationSecure = $null
}