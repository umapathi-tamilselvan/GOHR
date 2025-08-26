#!/bin/bash

# GOHR Development Workflow Script
# This script streamlines the development process with automode

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Function to print colored output
print_header() {
    echo -e "${PURPLE}================================${NC}"
    echo -e "${PURPLE}  GOHR Development Workflow${NC}"
    echo -e "${PURPLE}================================${NC}"
}

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

print_step() {
    echo -e "${CYAN}[STEP]${NC} $1"
}

# Function to check project status
check_project_status() {
    print_step "Checking project status..."
    
    # Check if we're in the right directory
    if [ ! -f "composer.json" ] || [ ! -f "package.json" ]; then
        print_error "Not in GOHR project root. Please run this script from the project root."
        exit 1
    fi
    
    # Check git status
    if [ -d ".git" ]; then
        print_status "Git repository found"
        git_status=$(git status --porcelain)
        if [ -z "$git_status" ]; then
            print_success "Working directory is clean"
        else
            print_warning "Working directory has changes:"
            echo "$git_status"
        fi
    else
        print_warning "Git repository not found"
    fi
    
    # Check dependencies
    if [ -d "vendor" ]; then
        print_success "PHP dependencies installed"
    else
        print_warning "PHP dependencies not installed. Run: composer install"
    fi
    
    if [ -d "node_modules" ]; then
        print_success "Node.js dependencies installed"
    else
        print_warning "Node.js dependencies not installed. Run: npm install"
    fi
}

# Function to start development environment
start_dev_environment() {
    print_step "Starting development environment..."
    
    # Start Laravel server
    print_status "Starting Laravel development server..."
    if ! pgrep -f "php artisan serve" > /dev/null; then
        php artisan serve --host=0.0.0.0 --port=8000 > /dev/null 2>&1 &
        print_success "Laravel server started on http://localhost:8000"
    else
        print_status "Laravel server already running"
    fi
    
    # Start Vite dev server
    print_status "Starting Vite development server..."
    if ! pgrep -f "npm run dev" > /dev/null; then
        npm run dev > /dev/null 2>&1 &
        print_success "Vite dev server started"
    else
        print_status "Vite dev server already running"
    fi
    
    # Wait a moment for servers to start
    sleep 2
    
    # Check if servers are running
    if curl -s http://localhost:8000 > /dev/null; then
        print_success "Laravel server is responding"
    else
        print_warning "Laravel server may not be ready yet"
    fi
}

# Function to run tests
run_tests() {
    print_step "Running tests..."
    
    # Run PHP tests
    print_status "Running PHP tests..."
    if php artisan test; then
        print_success "PHP tests passed"
    else
        print_error "PHP tests failed"
        return 1
    fi
    
    # Run frontend tests if available
    if [ -f "package.json" ] && grep -q "test" package.json; then
        print_status "Running frontend tests..."
        if npm test; then
            print_success "Frontend tests passed"
        else
            print_warning "Frontend tests failed"
        fi
    fi
}

# Function to build project
build_project() {
    print_step "Building project..."
    
    # Build frontend assets
    print_status "Building frontend assets..."
    if npm run build; then
        print_success "Frontend build successful"
    else
        print_error "Frontend build failed"
        return 1
    fi
    
    # Clear Laravel caches
    print_status "Clearing Laravel caches..."
    php artisan cache:clear
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    print_success "Laravel caches cleared"
}

# Function to auto-commit changes
auto_commit_changes() {
    print_step "Auto-committing changes..."
    
    if [ -f "scripts/auto-commit.sh" ]; then
        ./scripts/auto-commit.sh
    else
        print_warning "Auto-commit script not found"
    fi
}

# Function to show development tips
show_dev_tips() {
    print_step "Development Tips:"
    echo ""
    echo -e "${CYAN}Quick Commands:${NC}"
    echo "  • Start dev: ./scripts/dev-workflow.sh start"
    echo "  • Run tests: ./scripts/dev-workflow.sh test"
    echo "  • Build: ./scripts/dev-workflow.sh build"
    echo "  • Auto-commit: ./scripts/auto-commit.sh"
    echo ""
    echo -e "${CYAN}Development URLs:${NC}"
    echo "  • Frontend: http://localhost:8000"
    echo "  • API: http://localhost:8000/api"
    echo "  • Health: http://localhost:8000/api/health"
    echo ""
    echo -e "${CYAN}Useful Commands:${NC}"
    echo "  • php artisan serve --port=8000"
    echo "  • npm run dev"
    echo "  • npm run build"
    echo "  • composer install"
    echo "  • npm install"
    echo ""
    echo -e "${CYAN}Automode Commands:${NC}"
    echo "  • 'Work in automode' - Enable continuous development"
    echo "  • 'Continue' - Continue with next logical step"
    echo "  • 'Review progress' - Check current status"
}

# Function to show help
show_help() {
    print_header
    echo ""
    echo "Usage: $0 [COMMAND]"
    echo ""
    echo "Commands:"
    echo "  start     Start development environment"
    echo "  status    Check project status"
    echo "  test      Run all tests"
    echo "  build     Build project for production"
    echo "  commit    Auto-commit changes"
    echo "  tips      Show development tips"
    echo "  help      Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0 start      # Start development servers"
    echo "  $0 status     # Check project status"
    echo "  $0 test       # Run tests"
    echo "  $0 build      # Build project"
    echo "  $0 commit     # Auto-commit changes"
}

# Main function
main() {
    print_header
    
    case "$1" in
        start)
            check_project_status
            start_dev_environment
            print_success "Development environment started!"
            show_dev_tips
            ;;
        status)
            check_project_status
            ;;
        test)
            run_tests
            ;;
        build)
            build_project
            ;;
        commit)
            auto_commit_changes
            ;;
        tips)
            show_dev_tips
            ;;
        help|--help|-h)
            show_help
            ;;
        "")
            show_help
            ;;
        *)
            print_error "Unknown command: $1"
            show_help
            exit 1
            ;;
    esac
}

# Run main function
main "$@" 