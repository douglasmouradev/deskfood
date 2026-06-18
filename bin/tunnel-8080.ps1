# Expõe o servidor local na internet (HTTPS) quando o Wi-Fi bloqueia celular→PC.
# Uso: .\bin\tunnel-8080.ps1
# Requer: servidor PHP já rodando em 0.0.0.0:8080

Write-Host "Iniciando tunel HTTPS (localtunnel) na porta 8080..."
Write-Host "Mantenha esta janela aberta. A URL aparecera abaixo."
Write-Host ""
Write-Host "No celular, abra a URL https://....loca.lt (pode pedir IP no primeiro acesso)."
Write-Host "Para GPS real, HTTPS e melhor que HTTP na rede local."
Write-Host ""

npx --yes localtunnel --port 8080
