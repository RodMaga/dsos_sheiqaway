#!/bin/bash
# Script de Verificação da API Sheiqhome

echo "╔════════════════════════════════════════════════════════════╗"
echo "║         VERIFICAÇÃO DA API SHEIQHOME - NODE.JS            ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

# Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Contadores
TOTAL=0
PASSED=0

# Função para testar
test_check() {
    TOTAL=$((TOTAL+1))
    if [ -f "$1" ] || [ -d "$1" ]; then
        echo -e "${GREEN}✅${NC} $2"
        PASSED=$((PASSED+1))
    else
        echo -e "${RED}❌${NC} $2"
    fi
}

echo "🔍 Verificando estrutura de ficheiros..."
echo ""

test_check "server.js" "Servidor principal (server.js)"
test_check "package.json" "Ficheiro de dependências (package.json)"
test_check "README.md" "Documentação README.md"
test_check "API_DOCUMENTATION.md" "Documentação de API"
test_check "TESTS.md" "Resultados de testes"
test_check "SETUP.md" "Instruções de entrega"
test_check "SUMMARY.md" "Sumário de implementação"
test_check ".gitignore" "Ficheiro .gitignore"

echo ""
echo "🔍 Verificando pasta src/..."
echo ""

test_check "src/database.js" "Módulo de base de dados"
test_check "src/models/User.js" "Modelo User"
test_check "src/models/Hotel.js" "Modelo Hotel"
test_check "src/models/Reservation.js" "Modelo Reservation"
test_check "src/controllers/hotelController.js" "Controller de hotéis"
test_check "src/controllers/reservationController.js" "Controller de reservas"
test_check "src/routes/hotels.js" "Rotas de hotéis"
test_check "src/routes/reservations.js" "Rotas de reservas"
test_check "src/seeders/seedDatabase.js" "Seeder de dados"

echo ""
echo "🔍 Verificando dependências..."
echo ""

if [ -d "node_modules" ]; then
    echo -e "${GREEN}✅${NC} Dependências instaladas (node_modules/)"
    PASSED=$((PASSED+1))
else
    echo -e "${RED}❌${NC} Dependências não instaladas (execute: npm install)"
fi
TOTAL=$((TOTAL+1))

echo ""
echo "╔════════════════════════════════════════════════════════════╗"
echo "║                    RESUMO DA VERIFICAÇÃO                   ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

echo "✅ Ficheiros verificados: $PASSED/$TOTAL"
echo ""

if [ $PASSED -eq $TOTAL ]; then
    echo -e "${GREEN}✨ ESTRUTURA COMPLETA E VÁLIDA! ✨${NC}"
    echo ""
    echo "Próximos passos:"
    echo "1. npm install          (se não executado)"
    echo "2. node server.js       (para iniciar o servidor)"
    echo "3. Testar em http://localhost:3000"
else
    echo -e "${YELLOW}⚠️  Alguns ficheiros estão faltando. Verifique a estrutura.${NC}"
fi

echo ""
echo "════════════════════════════════════════════════════════════"
