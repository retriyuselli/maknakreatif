# 🔑 SETUP GITHUB PERSONAL ACCESS TOKEN

## MASALAH

```
remote: Invalid username or token. Password authentication is not supported for Git operations.
fatal: Authentication failed for 'https://github.com/retriyuselli/maknakreatif.git/'
```

GitHub tidak lagi mendukung password authentication untuk Git operations sejak August 2021.

## ✅ SOLUSI: PERSONAL ACCESS TOKEN (PAT)

### STEP 1: BUAT PERSONAL ACCESS TOKEN

1. **Buka GitHub Settings:**

    - Go to: https://github.com/settings/tokens
    - Atau: GitHub Profile → Settings → Developer settings → Personal access tokens → Tokens (classic)

2. **Generate New Token:**

    - Click "Generate new token (classic)"
    - Note: "Makna Kreatif Laravel App"
    - Expiration: 90 days (atau sesuai kebutuhan)

3. **Select Scopes (Permissions):**

    ```
    ✅ repo (Full control of private repositories)
    ✅ workflow (Update GitHub Action workflows)
    ✅ write:packages (Upload packages to GitHub Package Registry)
    ✅ delete:packages (Delete packages from GitHub Package Registry)
    ```

4. **Generate Token:**
    - Click "Generate token"
    - **COPY TOKEN IMMEDIATELY** (hanya ditampilkan sekali!)

### STEP 2: GUNAKAN TOKEN SEBAGAI PASSWORD

#### Opsi A: Langsung di Terminal

```bash
git push origin main
# Username: retriyuselli@gmail.com
# Password: [PASTE_TOKEN_HERE]
```

#### Opsi B: Update Git Remote dengan Token

```bash
# Remove existing remote
git remote remove origin

# Add remote with token
git remote add origin https://[TOKEN]@github.com/retriyuselli/maknakreatif.git

# Push
git push -u origin main
```

#### Opsi C: Configure Git Credential Manager

```bash
# Set credential helper
git config --global credential.helper store

# First push (akan diminta username/token sekali)
git push origin main
# Username: retriyuselli@gmail.com
# Password: [TOKEN]

# Push berikutnya otomatis
```

### STEP 3: SIMPAN TOKEN DENGAN AMAN

**⚠️ PENTING: JANGAN SHARE TOKEN KE SIAPAPUN!**

Simpan token di:

-   Password manager (1Password, LastPass, dll)
-   File terenkripsi
-   Environment variable (untuk CI/CD)

## 🔧 ALTERNATIVE: SSH KEY (RECOMMENDED)

SSH key lebih aman dan tidak expire:

### Setup SSH Key:

```bash
# Generate SSH key
ssh-keygen -t ed25519 -C "retriyuselli@gmail.com"

# Add to SSH agent
eval "$(ssh-agent -s)"
ssh-add ~/.ssh/id_ed25519

# Copy public key
cat ~/.ssh/id_ed25519.pub
```

### Add to GitHub:

1. Go to: https://github.com/settings/keys
2. Click "New SSH key"
3. Paste public key content
4. Save

### Update Git Remote:

```bash
# Change remote URL to SSH
git remote set-url origin git@github.com:retriyuselli/maknakreatif.git

# Test connection
ssh -T git@github.com

# Push
git push origin main
```

## 🚀 QUICK FIX SCRIPT

Jalankan script ini untuk setup otomatis:

```bash
#!/bin/bash
echo "🔑 GitHub Authentication Setup"

# Check if token is provided
if [ -z "$1" ]; then
    echo "Usage: ./github-auth-fix.sh [YOUR_PERSONAL_ACCESS_TOKEN]"
    echo "Get token from: https://github.com/settings/tokens"
    exit 1
fi

TOKEN=$1
REPO_URL="https://${TOKEN}@github.com/retriyuselli/maknakreatif.git"

# Update remote URL with token
git remote set-url origin $REPO_URL

echo "✅ Remote URL updated with token"
echo "🚀 Testing push..."

# Test push
git push origin main

if [ $? -eq 0 ]; then
    echo "✅ Push successful!"
else
    echo "❌ Push failed. Check token permissions."
fi
```

## 📝 TROUBLESHOOTING

### Error: "token does not have required permissions"

-   Go back to GitHub → Settings → Personal access tokens
-   Edit your token and add "repo" scope

### Error: "repository not found"

-   Check repository name: `maknakreatif`
-   Ensure token has access to the repository

### Error: "support for password authentication was removed"

-   You're still using password instead of token
-   Make sure to use TOKEN as password, not your GitHub password

## 🎯 RECOMMENDED WORKFLOW

1. **Development (Local):** Use SSH key
2. **CI/CD (Server):** Use Personal Access Token in environment variables
3. **Team Members:** Each person creates their own PAT

---

**Dibuat:** September 10, 2025  
**Updated:** Sesuai GitHub Auth requirements 2024
