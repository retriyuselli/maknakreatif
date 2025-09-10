#!/bin/bash

echo "🔑 GITHUB AUTHENTICATION QUICK FIX"
echo "=================================="

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}📋 GitHub tidak lagi mendukung password authentication${NC}"
echo -e "${BLUE}   Anda perlu Personal Access Token (PAT)${NC}"
echo ""

# Check if token is provided as argument
if [ -z "$1" ]; then
    echo -e "${YELLOW}💡 CARA MENDAPATKAN PERSONAL ACCESS TOKEN:${NC}"
    echo "1. Buka: https://github.com/settings/tokens"
    echo "2. Click 'Generate new token (classic)'"
    echo "3. Select scopes: repo, workflow"
    echo "4. Generate dan copy token"
    echo ""
    echo -e "${YELLOW}🚀 CARA MENGGUNAKAN SCRIPT INI:${NC}"
    echo "./github-auth-setup.sh [YOUR_TOKEN]"
    echo ""
    echo -e "${YELLOW}📖 Atau baca panduan lengkap di: GITHUB_AUTH_FIX.md${NC}"
    exit 1
fi

TOKEN=$1
REPO_URL="https://${TOKEN}@github.com/retriyuselli/maknakreatif.git"

echo -e "${YELLOW}🔧 Updating Git remote with token...${NC}"

# Backup current remote
CURRENT_REMOTE=$(git remote get-url origin 2>/dev/null)
if [ $? -eq 0 ]; then
    echo "Current remote: $CURRENT_REMOTE"
fi

# Update remote URL with token
git remote set-url origin $REPO_URL

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Remote URL updated successfully${NC}"
else
    echo -e "${RED}❌ Failed to update remote URL${NC}"
    exit 1
fi

echo -e "${YELLOW}🚀 Testing authentication...${NC}"

# Test with a simple fetch
git fetch origin >/dev/null 2>&1

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Authentication successful!${NC}"
    
    # Check if there are changes to push
    if git status --porcelain | grep -q .; then
        echo -e "${YELLOW}📤 Found local changes. Attempting to push...${NC}"
        
        # Add all changes
        git add .
        
        # Commit if there are staged changes
        if git diff --cached --quiet; then
            echo -e "${BLUE}ℹ️ No changes to commit${NC}"
        else
            echo -e "${YELLOW}💾 Committing changes...${NC}"
            git commit -m "Security fixes and documentation updates - $(date '+%Y-%m-%d %H:%M')"
        fi
        
        # Push
        echo -e "${YELLOW}📤 Pushing to GitHub...${NC}"
        git push origin main
        
        if [ $? -eq 0 ]; then
            echo -e "${GREEN}🎉 Push successful!${NC}"
        else
            echo -e "${RED}❌ Push failed. Check token permissions.${NC}"
            echo -e "${YELLOW}💡 Make sure token has 'repo' scope${NC}"
        fi
    else
        echo -e "${BLUE}ℹ️ No local changes to push${NC}"
    fi
    
else
    echo -e "${RED}❌ Authentication failed${NC}"
    echo -e "${YELLOW}💡 Possible issues:${NC}"
    echo "   - Token expired or invalid"
    echo "   - Token doesn't have 'repo' scope"
    echo "   - Repository name incorrect"
    echo ""
    echo -e "${YELLOW}🔗 Create new token: https://github.com/settings/tokens${NC}"
fi

echo ""
echo -e "${BLUE}📚 For detailed guide, check: GITHUB_AUTH_FIX.md${NC}"
