# Guia rápido: celular acessando o Desk Food quando o Wi-Fi corporativo isola dispositivos.
# Execute: .\bin\serve-mobile.ps1

$wifiIp = (Get-NetIPAddress -AddressFamily IPv4 -InterfaceAlias 'Wi-Fi' -ErrorAction SilentlyContinue |
    Where-Object { $_.IPAddress -like '192.168.*' } | Select-Object -First 1).IPAddress

$hotspotIp = (Get-NetIPAddress -AddressFamily IPv4 |
    Where-Object { $_.InterfaceAlias -like '*Local Area Connection*' -or $_.InterfaceAlias -like '*Conex*o Local*' -or $_.IPAddress -like '192.168.137.*' } |
    Select-Object -First 1).IPAddress

Write-Host ""
Write-Host "=== Desk Food — acesso pelo celular ===" -ForegroundColor Cyan
Write-Host ""

if ($wifiIp) {
    Write-Host "Opcao A — mesma Wi-Fi (se o roteador NAO isolar aparelhos):" -ForegroundColor Yellow
    Write-Host "  http://${wifiIp}:8080/u/centro"
    Write-Host ""
}

Write-Host "Opcao B — Hotspot do notebook (recomendado em rede corporativa):" -ForegroundColor Green
Write-Host "  1. Windows: Configuracoes > Rede > Ponto de acesso movel > Ativar"
Write-Host "  2. No celular, conecte no Wi-Fi do notebook (nao use 4G)"
if ($hotspotIp) {
    Write-Host "  3. Abra: http://${hotspotIp}:8080/u/centro"
} else {
    Write-Host "  3. Abra: http://192.168.137.1:8080/u/centro  (IP padrao do hotspot Windows)"
}
Write-Host ""

Write-Host "Opcao C — Tunel HTTPS (funciona em qualquer rede, inclusive 4G):" -ForegroundColor Magenta
Write-Host "  Em outro terminal: .\bin\tunnel-8080.ps1"
Write-Host "  Use a URL https://....loca.lt no celular"
Write-Host ""

Write-Host "Servidor deve estar rodando:" -ForegroundColor Gray
Write-Host "  php -S 0.0.0.0:8080 -t public public/router.php"
Write-Host ""

Write-Host "Firewall (uma vez, como Admin):" -ForegroundColor Gray
Write-Host "  .\bin\open-firewall-8080.ps1"
Write-Host ""
