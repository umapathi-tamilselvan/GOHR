#!/bin/bash

# GOHR Auto-Commit Script
# This script automatically commits changes after major feature completion

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to check if git repository exists
check_git_repo() {
    if [ ! -d ".git" ]; then
        print_error "Not a git repository. Please run this script from the project root."
        exit 1
    fi
}

# Function to check if there are changes to commit
check_changes() {
    if [ -z "$(git status --porcelain)" ]; then
        print_warning "No changes to commit."
        return 1
    fi
    return 0
}

# Function to get commit message based on changes
get_commit_message() {
    local changed_files=$(git status --porcelain | awk '{print $2}')
    local message=""
    
    # Check for different types of changes
    if echo "$changed_files" | grep -q "\.vue$"; then
        message="feat: update Vue.js components"
    elif echo "$changed_files" | grep -q "\.php$"; then
        message="feat: update PHP backend"
    elif echo "$changed_files" | grep -q "\.js$"; then
        message="feat: update JavaScript functionality"
    elif echo "$changed_files" | grep -q "\.css$"; then
        message="feat: update styling"
    elif echo "$changed_files" | grep -q "\.md$"; then
        message="docs: update documentation"
    elif echo "$changed_files" | grep -q "\.sql$"; then
        message="feat: update database schema"
    else
        message="feat: update project files"
    fi
    
    echo "$message"
}

# Function to perform auto-commit
auto_commit() {
    local commit_message="$1"
    
    print_status "Staging all changes..."
    git add .
    
    print_status "Committing changes with message: $commit_message"
    if git commit -m "$commit_message"; then
        print_success "Changes committed successfully!"
        return 0
    else
        print_error "Failed to commit changes."
        return 1
    fi
}

# Function to push changes (optional)
push_changes() {
    print_status "Pushing changes to remote repository..."
    if git push; then
        print_success "Changes pushed successfully!"
    else
        print_warning "Failed to push changes. You may need to pull first."
    fi
}

# Function to show git status
show_status() {
    print_status "Current git status:"
    git status --short
}

# Function to show recent commits
show_recent_commits() {
    print_status "Recent commits:"
    git log --oneline -5
}

# Main function
main() {
    print_status "GOHR Auto-Commit Script Starting..."
    
    # Check if we're in a git repository
    check_git_repo
    
    # Check if there are changes to commit
    if ! check_changes; then
        exit 0
    fi
    
    # Show current status
    show_status
    
    # Get commit message
    local commit_message=$(get_commit_message)
    print_status "Auto-generated commit message: $commit_message"
    
    # Perform auto-commit
    if auto_commit "$commit_message"; then
        # Show recent commits
        show_recent_commits
        
        # Ask if user wants to push
        read -p "Do you want to push changes to remote? (y/N): " -n 1 -r
        echo
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            push_changes
        else
            print_status "Changes committed locally. Use 'git push' when ready."
        fi
    fi
}

# Function to show help
show_help() {
    echo "GOHR Auto-Commit Script"
    echo ""
    echo "Usage: $0 [OPTIONS]"
    echo ""
    echo "Options:"
    echo "  -h, --help     Show this help message"
    echo "  -s, --status   Show git status only"
    echo "  -l, --log      Show recent commits only"
    echo "  -p, --push     Commit and push changes"
    echo "  -m, --message  Use custom commit message"
    echo ""
    echo "Examples:"
    echo "  $0                    # Auto-commit with generated message"
    echo "  $0 -s                 # Show git status"
    echo "  $0 -l                 # Show recent commits"
    echo "  $0 -p                 # Commit and push"
    echo "  $0 -m 'Custom message' # Commit with custom message"
}

# Parse command line arguments
case "$1" in
    -h|--help)
        show_help
        exit 0
        ;;
    -s|--status)
        check_git_repo
        show_status
        exit 0
        ;;
    -l|--log)
        check_git_repo
        show_recent_commits
        exit 0
        ;;
    -p|--push)
        check_git_repo
        if check_changes; then
            commit_message=$(get_commit_message)
            auto_commit "$commit_message" && push_changes
        fi
        exit 0
        ;;
    -m|--message)
        if [ -z "$2" ]; then
            print_error "Commit message is required with -m option"
            exit 1
        fi
        check_git_repo
        if check_changes; then
            auto_commit "$2"
        fi
        exit 0
        ;;
    "")
        main
        ;;
    *)
        print_error "Unknown option: $1"
        show_help
        exit 1
        ;;
esac 