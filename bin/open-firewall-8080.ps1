# Libera a porta 8080 para o celular acessar o Desk Food na rede local.
# Clique com botão direito → "Executar com PowerShell" como Administrador.
# Ou: PowerShell (Admin) → cd pasta do projeto → .\bin\open-firewall-8080.ps1

$ruleName = 'Desk Food PHP 8080'
$existing = Get-NetFirewallRule -DisplayName $ruleName -ErrorAction SilentlyContinue
if ($existing) {
    Write-Host "Regra ja existe: $ruleName"
} else {
    New-NetFirewallRule -DisplayName $ruleName -Direction Inbound -Action Allow -Protocol TCP -LocalPort 8080 -Profile Private,Public,Domain | Out-Null
    Write-Host "Regra criada: porta 8080 liberada (rede Privada, Publica e Dominio)."
}

$ip = (Get-NetIPAddress -AddressFamily IPv4 | Where-Object {
    $_.IPAddress -like '192.168.*' -and $_.PrefixOrigin -ne 'WellKnown'
} | Select-Object -First 1).IPAddress

if (-not $ip) {
    $ip = '192.168.100.181'
}

Write-Host ""
Write-Host "No CELULAR (mesma Wi-Fi), abra:"
Write-Host "  http://${ip}:8080/u/centro"
Write-Host ""
Write-Host "IP do PC nesta rede: $ip"
Write-Host "Nao use localhost nem o IP do celular."
