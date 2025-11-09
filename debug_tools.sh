#!/bin/bash

# 调试查看工具
# 使用方法: ./debug_tools.sh [command]

LOG_FILE="runtime.log"

case $1 in
    "tail")
        echo "=== 实时查看调试日志 ==="
        if [ -f "$LOG_FILE" ]; then
            tail -f "$LOG_FILE"
        else
            echo "日志文件不存在: $LOG_FILE"
        fi
        ;;
    "show")
        echo "=== 显示最新50行调试日志 ==="
        if [ -f "$LOG_FILE" ]; then
            tail -50 "$LOG_FILE"
        else
            echo "日志文件不存在: $LOG_FILE"
        fi
        ;;
    "clear")
        echo "=== 清空调试日志 ==="
        if [ -f "$LOG_FILE" ]; then
            echo "清空调试日志文件..."
            echo "" > "$LOG_FILE"
            echo "日志已清空"
        else
            echo "日志文件不存在: $LOG_FILE"
        fi
        ;;
    "search")
        if [ -z "$2" ]; then
            echo "使用方法: $0 search [搜索关键词]"
            exit 1
        fi
        echo "=== 搜索调试日志中的关键词: $2 ==="
        if [ -f "$LOG_FILE" ]; then
            grep -n "$2" "$LOG_FILE"
        else
            echo "日志文件不存在: $LOG_FILE"
        fi
        ;;
    "simclient")
        echo "=== SimClient相关调试日志 ==="
        if [ -f "$LOG_FILE" ]; then
            grep -n -i "simclient\|接码\|订单" "$LOG_FILE"
        else
            echo "日志文件不存在: $LOG_FILE"
        fi
        ;;
    "user")
        echo "=== 用户相关调试日志 ==="
        if [ -f "$LOG_FILE" ]; then
            grep -n -i "用户\|登录" "$LOG_FILE"
        else
            echo "日志文件不存在: $LOG_FILE"
        fi
        ;;
    "help"|"")
        echo "=== 调试工具帮助 ==="
        echo "使用方法: $0 [command]"
        echo ""
        echo "命令说明:"
        echo "  tail        - 实时查看调试日志 (按Ctrl+C退出)"
        echo "  show        - 显示最新50行日志"
        echo "  clear       - 清空调试日志"
        echo "  search KEY  - 搜索包含KEY的日志"
        echo "  simclient   - 查看SimClient相关日志"
        echo "  user        - 查看用户相关日志"
        echo "  help        - 显示此帮助信息"
        echo ""
        echo "示例:"
        echo "  $0 tail                    # 实时查看日志"
        echo "  $0 search 错误             # 搜索包含'错误'的日志"
        echo "  $0 simclient               # 查看SimClient日志"
        ;;
    *)
        echo "未知命令: $1"
        echo "使用 '$0 help' 查看帮助"
        exit 1
        ;;
esac