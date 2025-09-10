#!/bin/bash

echo "🔍 GIT PULL DIAGNOSTIC TOOL"
echo "============================"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Check if in git repo
if [ ! -d ".git" ]; then
    echo -e "${RED}❌ Not in a git repository!${NC}"
    exit 1
fi

echo -e "${BLUE}📍 Current Directory:${NC} $(pwd)"
echo ""

# 1. Check current branch
echo -e "${YELLOW}🌿 BRANCH STATUS:${NC}"
CURRENT_BRANCH=$(git branch --show-current)
echo "Current branch: $CURRENT_BRANCH"
git branch -a
echo ""

# 2. Check remote configuration
echo -e "${YELLOW}🔗 REMOTE CONFIGURATION:${NC}"
git remote -v
echo ""

# 3. Check repository status
echo -e "${YELLOW}📊 REPOSITORY STATUS:${NC}"
git status --porcelain
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Working directory clean${NC}"
else
    echo -e "${RED}⚠️ There are uncommitted changes${NC}"
fi
echo ""

# 4. Check last commits
echo -e "${YELLOW}📝 RECENT COMMITS (LOCAL):${NC}"
git log --oneline -5
echo ""

# 5. Fetch and check remote commits
echo -e "${YELLOW}🌐 FETCHING REMOTE CHANGES...${NC}"
git fetch origin
echo ""

echo -e "${YELLOW}📝 RECENT COMMITS (REMOTE):${NC}"
git log origin/main --oneline -5
echo ""

# 6. Compare local vs remote
echo -e "${YELLOW}🔄 COMPARING LOCAL vs REMOTE:${NC}"
LOCAL_COMMIT=$(git rev-parse HEAD)
REMOTE_COMMIT=$(git rev-parse origin/main)

if [ "$LOCAL_COMMIT" = "$REMOTE_COMMIT" ]; then
    echo -e "${GREEN}✅ Local and remote are in sync${NC}"
else
    echo -e "${YELLOW}⚠️ Local and remote are out of sync${NC}"
    echo "Local:  $LOCAL_COMMIT"
    echo "Remote: $REMOTE_COMMIT"
    
    # Check if local is behind
    if git merge-base --is-ancestor HEAD origin/main; then
        echo -e "${BLUE}📥 Local is BEHIND remote (need to pull)${NC}"
    else
        echo -e "${BLUE}📤 Local is AHEAD of remote (need to push)${NC}"
    fi
fi
echo ""

# 7. Check for specific files that should exist after pull
echo -e "${YELLOW}📁 CHECKING FOR EXPECTED FILES:${NC}"
EXPECTED_FILES=(
    "SECURITY_AUDIT_REPORT.md"
    "GITHUB_AUTH_FIX.md"
    "fix-production.sh"
    "GIT_PULL_TROUBLESHOOTING.md"
    ".env.production.example"
)

for file in "${EXPECTED_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo -e "${GREEN}✅ $file exists${NC}"
    else
        echo -e "${RED}❌ $file missing${NC}"
    fi
done
echo ""

# 8. Suggested actions
echo -e "${YELLOW}🎯 SUGGESTED ACTIONS:${NC}"

if [ "$CURRENT_BRANCH" != "main" ]; then
    echo "1. Switch to main branch: git checkout main"
fi

if [ "$LOCAL_COMMIT" != "$REMOTE_COMMIT" ]; then
    echo "2. Pull latest changes: git pull origin main"
fi

# Check for conflicts
git merge-tree $(git merge-base HEAD origin/main) HEAD origin/main > /dev/null 2>&1
if [ $? -ne 0 ]; then
    echo -e "${RED}⚠️ WARNING: Potential merge conflicts detected${NC}"
    echo "3. Resolve conflicts manually if pull fails"
fi

echo ""
echo -e "${GREEN}🔧 QUICK FIX COMMANDS:${NC}"
echo "git checkout main"
echo "git fetch origin"
echo "git pull origin main"
echo ""
echo -e "${YELLOW}💡 If still no changes, try:${NC}"
echo "git reset --hard origin/main"
