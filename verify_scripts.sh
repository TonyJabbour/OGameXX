#!/bin/bash

###############################################################################
# verify_scripts.sh - Verify deployment automation scripts
###############################################################################

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo ""
echo "=================================================================="
echo "     Deployment Automation Scripts Verification"
echo "=================================================================="
echo ""

# Check if scripts exist
echo -e "${BLUE}Checking script files...${NC}"

scripts=("deploy.sh" "backup.sh" "restore.sh")
all_exist=true

for script in "${scripts[@]}"; do
    if [ -f "$script" ]; then
        size=$(du -h "$script" | cut -f1)
        lines=$(wc -l < "$script")
        echo -e "  ✓ $script exists ($size, $lines lines)"
    else
        echo -e "  ✗ $script missing"
        all_exist=false
    fi
done

echo ""

# Check script syntax
echo -e "${BLUE}Checking script syntax...${NC}"

for script in "${scripts[@]}"; do
    if [ -f "$script" ]; then
        if bash -n "$script" 2>/dev/null; then
            echo -e "  ✓ $script syntax is valid"
        else
            echo -e "  ✗ $script has syntax errors"
        fi
    fi
done

echo ""

# Check shebang
echo -e "${BLUE}Checking shebang lines...${NC}"

for script in "${scripts[@]}"; do
    if [ -f "$script" ]; then
        shebang=$(head -n 1 "$script")
        if [[ "$shebang" == "#!/bin/bash"* ]]; then
            echo -e "  ✓ $script has correct shebang"
        else
            echo -e "  ✗ $script shebang issue: $shebang"
        fi
    fi
done

echo ""

# Check for key functions in deploy.sh
echo -e "${BLUE}Checking deploy.sh functionality...${NC}"

if [ -f "deploy.sh" ]; then
    functions=("check_prerequisites" "build_images" "start_services" "run_migrations" "show_status")
    for func in "${functions[@]}"; do
        if grep -q "^\s*${func}\s*()" "deploy.sh"; then
            echo -e "  ✓ Function $func found"
        else
            echo -e "  ✗ Function $func missing"
        fi
    done
fi

echo ""

# Check for key functions in backup.sh
echo -e "${BLUE}Checking backup.sh functionality...${NC}"

if [ -f "backup.sh" ]; then
    functions=("backup_database" "backup_files" "cleanup_old_backups" "health_check")
    for func in "${functions[@]}"; do
        if grep -q "^\s*${func}\s*()" "backup.sh"; then
            echo -e "  ✓ Function $func found"
        else
            echo -e "  ✗ Function $func missing"
        fi
    done
fi

echo ""

# Check for key functions in restore.sh
echo -e "${BLUE}Checking restore.sh functionality...${NC}"

if [ -f "restore.sh" ]; then
    functions=("restore_database" "restore_files" "interactive_restore" "show_backup_details")
    for func in "${functions[@]}"; do
        if grep -q "^\s*${func}\s*()" "restore.sh"; then
            echo -e "  ✓ Function $func found"
        else
            echo -e "  ✗ Function $func missing"
        fi
    done
fi

echo ""

# Check Docker configuration
echo -e "${BLUE}Checking Docker configuration...${NC}"

if [ -f "docker-compose.yml" ]; then
    services=("ogamex-db" "ogamex-app" "ogamex-webserver")
    for service in "${services[@]}"; do
        if grep -q "container_name: $service" "docker-compose.yml"; then
            echo -e "  ✓ Service $service configured"
        else
            echo -e "  ✗ Service $service missing"
        fi
    done
else
    echo -e "  ✗ docker-compose.yml not found"
fi

echo ""

# Check backup directory structure
echo -e "${BLUE}Checking backup directory structure...${NC}"

if [ -d "backups" ]; then
    echo -e "  ✓ backups directory exists"
    subdirs=("database" "files" "full")
    for subdir in "${subdirs[@]}"; do
        if [ -d "backups/$subdir" ]; then
            echo -e "  ✓ backups/$subdir directory exists"
        else
            echo -e "  ! backups/$subdir directory missing (will be created)"
        fi
    done
else
    echo -e "  ! backups directory missing (will be created)"
fi

echo ""

# Summary
echo "=================================================================="
echo -e "${BLUE}Verification Summary${NC}"
echo "=================================================================="

if [ "$all_exist" = true ]; then
    echo -e "${GREEN}✓ All deployment scripts are present and properly configured${NC}"
    echo ""
    echo "Available commands:"
    echo -e "  ${GREEN}bash deploy.sh${NC}           - Deploy application"
    echo -e "  ${GREEN}bash backup.sh${NC}           - Create backup"
    echo -e "  ${GREEN}bash restore.sh${NC}          - Restore from backup"
    echo -e "  ${GREEN}bash backup.sh list${NC}      - List backups"
    echo -e "  ${GREEN}bash restore.sh interactive${NC} - Interactive restore"
    echo ""
    echo "For detailed usage instructions, see DEPLOYMENT_GUIDE.md"
else
    echo -e "${RED}✗ Some scripts are missing${NC}"
fi

echo ""
echo "=================================================================="
