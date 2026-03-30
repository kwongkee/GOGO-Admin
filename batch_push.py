#!/usr/bin/env python3
"""
分批将本地代码推送到 GitHub。
使用说明：
1. 确保已在仓库根目录运行过 `git init` 并配置好远程仓库。
2. 直接运行本脚本（放在仓库根目录或任意位置，修改 REPO_PATH 即可）。
3. 脚本会读取所有未跟踪文件（遵守 .gitignore），按 BATCH_SIZE 分批 add、commit、push。
4. 可重复运行，只会处理尚未提交的新文件。
"""

import os
import sys
import subprocess
import time
import shlex
from pathlib import Path

# ========== 配置参数 ==========
REPO_PATH = "/www/wwwroot/gogo"      # 仓库绝对路径
BATCH_SIZE = 100                      # 每批文件数
COMMIT_PREFIX = "Batch commit: "      # 提交信息前缀
PUSH_RETRY = 3                        # 推送失败重试次数
PUSH_DELAY = 5                        # 重试前等待秒数
BRANCH = "main"                       # 要推送的分支名（本地和远程同名）
REMOTE = "origin"                     # 远程仓库名称
# ==============================

def run_git_command(cmd, cwd=None, check=True, capture=True):
    """
    执行 git 命令，返回 (stdout, stderr, returncode)
    """
    try:
        result = subprocess.run(
            cmd,
            cwd=cwd or REPO_PATH,
            capture_output=capture,
            text=False,               # 不自动解码，保留 bytes
            check=False
        )
        return result.stdout, result.stderr, result.returncode
    except Exception as e:
        return None, str(e).encode(), -1

def get_untracked_files():
    """获取所有未跟踪文件（已遵守 .gitignore）"""
    # git ls-files --others --exclude-standard 输出每个文件一行
    stdout, stderr, code = run_git_command(
        ["git", "ls-files", "--others", "--exclude-standard"],
        capture=True
    )
    if code != 0:
        print(f"获取未跟踪文件失败: {stderr.decode('utf-8', errors='replace')}")
        sys.exit(1)

    # 将输出按行分割，每行是一个文件路径（可能包含非 UTF-8 字符）
    # 为了后续处理，我们保留原始字节，但在输出时尽量解码
    lines = stdout.split(b'\n')
    files = []
    for line in lines:
        if line:
            try:
                files.append(line.decode('utf-8'))
            except UnicodeDecodeError:
                # 无法解码的文件名，使用替换字符显示警告，但仍尝试处理（作为原始字节传递）
                print(f"警告：文件名包含非 UTF-8 字符，将尝试处理: {line!r}")
                files.append(line)   # 保留 bytes 类型
    return files

def git_add(files):
    """将文件添加到暂存区，files 可以是字符串列表或字节列表"""
    # 对于每个文件，使用 git add -- <file>，避免通配符问题
    for f in files:
        if isinstance(f, bytes):
            # 原始字节，直接作为参数传递（subprocess 会原样传递）
            cmd = ["git", "add", "--", f]
        else:
            cmd = ["git", "add", "--", f]
        _, stderr, code = run_git_command(cmd, capture=True)
        if code != 0:
            print(f"  添加文件 {f} 失败: {stderr.decode('utf-8', errors='replace')}")
            return False
    return True

def git_commit(message):
    """提交暂存区"""
    _, stderr, code = run_git_command(
        ["git", "commit", "-m", message],
        capture=True
    )
    if code != 0:
        print(f"  提交失败: {stderr.decode('utf-8', errors='replace')}")
        return False
    return True

def git_push():
    """推送到远程仓库"""
    for attempt in range(1, PUSH_RETRY + 1):
        _, stderr, code = run_git_command(
            ["git", "push", REMOTE, BRANCH],
            capture=True
        )
        if code == 0:
            print(f"  推送成功")
            return True
        else:
            print(f"  推送失败（第 {attempt} 次尝试）: {stderr.decode('utf-8', errors='replace')}")
            if attempt < PUSH_RETRY:
                time.sleep(PUSH_DELAY)
    print("  多次推送失败，请手动处理")
    return False

def main():
    # 检查仓库路径是否存在
    if not os.path.exists(REPO_PATH):
        print(f"错误：仓库路径 {REPO_PATH} 不存在")
        sys.exit(1)

    # 检查是否是 git 仓库
    _, _, code = run_git_command(["git", "rev-parse", "--git-dir"])
    if code != 0:
        print(f"错误：{REPO_PATH} 不是一个有效的 Git 仓库")
        sys.exit(1)

    # 获取未跟踪文件
    untracked = get_untracked_files()
    if not untracked:
        print("没有需要提交的新文件")
        return

    total = len(untracked)
    print(f"共发现 {total} 个未跟踪文件，将按每批 {BATCH_SIZE} 个文件分批提交")

    # 分批处理
    for i in range(0, total, BATCH_SIZE):
        batch_files = untracked[i:i+BATCH_SIZE]
        batch_num = i // BATCH_SIZE + 1
        print(f"\n批次 {batch_num}: 提交 {len(batch_files)} 个文件...")

        # 添加文件
        if not git_add(batch_files):
            print("  添加失败，跳过该批次")
            continue

        # 提交
        commit_msg = f"{COMMIT_PREFIX}添加 {len(batch_files)} 个文件（批次 {batch_num}）"
        if not git_commit(commit_msg):
            # 提交失败，回退暂存区
            run_git_command(["git", "reset", "HEAD"])
            continue

        # 推送
        if not git_push():
            print("  推送失败，请手动处理")
            # 可以选择中断或继续下一批
            # sys.exit(1)

        # 推送后稍等，避免 GitHub 限流
        time.sleep(1)

    print("\n所有批次处理完成。")

if __name__ == "__main__":
    main()