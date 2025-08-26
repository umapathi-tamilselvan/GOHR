# 🚀 GOHR Development Workflow

This document describes the automated development workflow for the GOHR HR Management System, including automode configuration, auto-commit functionality, and development tools.

## 📋 Table of Contents

- [Automode Configuration](#automode-configuration)
- [Auto-Commit System](#auto-commit-system)
- [Development Workflow Scripts](#development-workflow-scripts)
- [Git Hooks](#git-hooks)
- [Code Standards](#code-standards)
- [Quick Start Guide](#quick-start-guide)

## 🎯 Automode Configuration

### What is Automode?

Automode is a Cursor AI feature that enables continuous, automated development. When enabled, the AI assistant will:

- **Continue working automatically** without stopping for permission
- **Follow established patterns** and code standards
- **Implement features completely** before moving to the next
- **Maintain momentum** and continuous development
- **Auto-commit changes** after major feature completion

### How to Enable Automode

Simply say one of these phrases to the AI assistant:

- **"Work in automode"** - Enable continuous development
- **"Continue"** - Continue with next logical step
- **"Review automode progress"** - Check current status

### Automode Rules

The automode follows these rules defined in `.cursorrules`:

1. **Always reference documentation** before implementation
2. **Follow established patterns** and standards
3. **Maintain consistency** with existing codebase
4. **Implement comprehensive testing** for all features
5. **Document any deviations** from established patterns
6. **Continue working** until task completion

## 🔄 Auto-Commit System

### Overview

The auto-commit system automatically commits changes after major feature completion, ensuring:

- **Regular commits** with meaningful messages
- **Code quality checks** before committing
- **Conventional commit format** (feat:, fix:, docs:, etc.)
- **Optional auto-push** for feature commits

### Auto-Commit Script

```bash
# Basic auto-commit
./scripts/auto-commit.sh

# Show git status
./scripts/auto-commit.sh -s

# Show recent commits
./scripts/auto-commit.sh -l

# Commit and push
./scripts/auto-commit.sh -p

# Custom commit message
./scripts/auto-commit.sh -m "Custom message"
```

### Auto-Push Configuration

Auto-push is enabled by default for feature commits. To control this:

```bash
# Enable auto-push (default)
touch .auto-push

# Disable auto-push
rm .auto-push

# Override with environment variable
export AUTO_PUSH=false
```

## 🛠️ Development Workflow Scripts

### Main Workflow Script

```bash
# Show help
./scripts/dev-workflow.sh help

# Check project status
./scripts/dev-workflow.sh status

# Start development environment
./scripts/dev-workflow.sh start

# Run tests
./scripts/dev-workflow.sh test

# Build project
./scripts/dev-workflow.sh build

# Auto-commit changes
./scripts/dev-workflow.sh commit

# Show development tips
./scripts/dev-workflow.sh tips
```

### What Each Command Does

#### `start` - Start Development Environment
- Starts Laravel development server on port 8000
- Starts Vite dev server for frontend
- Checks server status
- Shows development tips

#### `status` - Check Project Status
- Verifies project structure
- Checks git repository status
- Validates dependencies
- Shows working directory status

#### `test` - Run All Tests
- Runs PHP tests with PHPUnit
- Runs frontend tests (if available)
- Reports test results

#### `build` - Build Project
- Builds frontend assets with Vite
- Clears Laravel caches
- Prepares for production

#### `commit` - Auto-Commit Changes
- Runs the auto-commit script
- Stages and commits changes
- Optionally pushes to remote

## 🔒 Git Hooks

### Pre-Commit Hook

Automatically runs before each commit to ensure code quality:

- **PHP syntax validation**
- **Vue.js syntax checking** (if ESLint available)
- **Large file detection** (>10MB warning)
- **Sensitive file protection** (.env, .key files)
- **TODO comment detection**
- **Commit message format validation**

### Post-Commit Hook

Runs after each commit for additional actions:

- **Commit information display**
- **Auto-push for feature commits** (if enabled)
- **Development status updates**
- **Git log summary**

### Hook Configuration

```bash
# Make hooks executable
chmod +x .git/hooks/pre-commit
chmod +x .git/hooks/post-commit

# Disable hooks temporarily
mv .git/hooks/pre-commit .git/hooks/pre-commit.disabled
mv .git/hooks/post-commit .git/hooks/post-commit.disabled
```

## 📏 Code Standards

### Commit Message Format

Follow conventional commit format:

```
type(scope): description

Examples:
feat: add user management module
fix: resolve authentication issue
docs: update API documentation
refactor: improve code structure
test: add comprehensive test coverage
chore: update dependencies
```

### Code Quality Requirements

- **PHP**: PSR-12 standards, proper validation, error handling
- **Vue.js**: Composition API, proper TypeScript, responsive design
- **Database**: Proper migrations, relationships, indexing
- **Security**: Authentication, authorization, input validation
- **Testing**: 90%+ coverage, comprehensive scenarios

## 🚀 Quick Start Guide

### 1. Initial Setup

```bash
# Clone the repository
git clone <repository-url>
cd GOHR

# Install dependencies
composer install
npm install

# Set up environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed
```

### 2. Start Development

```bash
# Start development environment
./scripts/dev-workflow.sh start

# Check status
./scripts/dev-workflow.sh status

# Open in browser
open http://localhost:8000
```

### 3. Development Workflow

```bash
# 1. Make changes to code
# 2. Test changes
./scripts/dev-workflow.sh test

# 3. Build if needed
./scripts/dev-workflow.sh build

# 4. Auto-commit changes
./scripts/dev-workflow.sh commit

# 5. Continue development
```

### 4. Automode Development

1. **Say "Work in automode"** to the AI assistant
2. **Describe the feature** you want to implement
3. **Let the AI continue** working automatically
4. **Monitor progress** with "Review automode progress"
5. **Changes are auto-committed** after completion

## 🔧 Configuration Files

### .cursorrules
Main configuration file for Cursor AI behavior, including:
- Project overview and architecture
- Code standards and patterns
- Development workflow rules
- Automode configuration

### .gitignore
Comprehensive gitignore file excluding:
- Dependencies and build outputs
- Environment and configuration files
- IDE and editor files
- Temporary and cache files

### .auto-push
Simple configuration file to enable/disable auto-push:
- File exists = auto-push enabled
- File removed = auto-push disabled

## 📊 Development Tips

### Best Practices

1. **Always use automode** for feature development
2. **Let auto-commit handle** regular commits
3. **Use conventional commit messages** for manual commits
4. **Run tests frequently** during development
5. **Check project status** before starting work
6. **Use development workflow scripts** for common tasks

### Troubleshooting

#### Auto-commit not working
```bash
# Check script permissions
chmod +x scripts/auto-commit.sh

# Check git hooks
chmod +x .git/hooks/pre-commit
chmod +x .git/hooks/post-commit
```

#### Development servers not starting
```bash
# Check if ports are in use
lsof -i :8000
lsof -i :5173

# Kill existing processes
pkill -f "php artisan serve"
pkill -f "npm run dev"
```

#### Git hooks not running
```bash
# Check hook permissions
ls -la .git/hooks/

# Reinstall hooks
chmod +x .git/hooks/*
```

## 🎉 Success Metrics

### Development Efficiency
- **Faster feature implementation** with automode
- **Consistent code quality** with automated checks
- **Regular commits** with auto-commit system
- **Streamlined workflow** with development scripts

### Code Quality
- **Automated syntax checking** with git hooks
- **Conventional commit messages** for better history
- **Comprehensive testing** requirements
- **Security best practices** enforcement

### Team Collaboration
- **Standardized development process**
- **Automated quality gates**
- **Clear commit history**
- **Consistent code standards**

## 🚀 Next Steps

1. **Enable automode** for your next feature
2. **Use development scripts** for common tasks
3. **Let auto-commit** handle regular commits
4. **Monitor progress** with status checks
5. **Contribute to workflow** improvements

---

**🎯 Goal: Streamlined, automated development workflow that maintains high code quality and enables rapid feature development!**

For questions or issues, refer to the `.cursorrules` file or use the development workflow scripts. 