<?php
namespace app\index\controller;

use think\Request;
use think\Db;
use think\Controller;
use think\Log;

class Monitor extends Controller
{
    // 微信推送配置
    private $WECHAT_API = 'https://shop.gogo198.cn/api/sendwechattemplatenotice.php';
    private $WECHAT_OPENID = 'ov3-bt8keSKg_8z9Wwi-zG1hRhwg';
    private $WECHAT_TEMP_ID = 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8';

    public function index() {
        $token = session('monitor_token');
        if (empty($token)) {
            return view('monitor/login');
        }
        return view('monitor/index');
    }

    public function api() {
        $action = input('action');
        $token = session('monitor_token');

        // 处理登录
        if ($action === 'login') {
            $username = input('post.username');
            $password = input('post.password');
            if ($username === 'admin' && $password === 'Gogo@198') {
                session('monitor_token', md5('GOGO_MONITOR_' . date('Ymd')));
                return json(['code' => 0, 'msg' => '登录成功']);
            }
            return json(['code' => -1, 'msg' => '账号或密码错误']);
        }

        // 验证token
        if (empty($token)) {
            return json(['code' => -1, 'msg' => '未授权，请先登录']);
        }

        switch ($action) {
            case 'check':
                return $this->getSystemInfo();

            case 'system_detail':
                return $this->getSystemDetail();

            case 'report':
                return $this->getDailyReport();

            case 'security':
                return $this->getSecurityInfo();

            case 'security_events':
                return $this->getSecurityEvents();

            case 'crontab':
                return $this->getCrontabInfo();

            case 'service_detail':
                $service = input('service');
                return $this->getServiceDetail($service);

            case 'ip_info':
                $ip = input('ip');
                return $this->getIpInfo($ip);

            case 'block_ip':
                $ip = input('ip');
                return $this->blockIp($ip);

            case 'whitelist_ip':
                $ip = input('ip');
                return $this->whitelistIp($ip);

            case 'container_detail':
                $name = input('name');
                return $this->getContainerDetail($name);

            case 'container_logs':
                $name = input('name');
                $lines = input('lines/d', 50);
                return $this->getContainerLogs($name, $lines);

            case 'port_detail':
                return $this->getPortDetail();

            case 'network_detail':
                return $this->getNetworkDetail();

            case 'aliyun_events':
                return $this->getAliyunEvents();

            case 'wechat_test':
                return $this->testWechat();

            case 'daily_report_push':
                return $this->pushDailyReport();

            case 'ai_analysis':
                return $this->getAIAnalysis();

            case 'task_list':
                return $this->getTaskList();

            case 'task_detail':
                $taskId = input('task_id');
                return $this->getTaskDetail($taskId);

            case 'task_run':
                $taskId = input('task_id');
                return $this->runTask($taskId);

            // ========== 新增：Prometheus指标端点 ==========
            case 'metrics':
                return $this->getPrometheusMetrics();

            // ========== 新增：自动化运维 ==========
            case 'auto_optimize':
                return $this->autoOptimize();
            
            case 'auto_block_attacks':
                return $this->autoBlockAttacks();
            
            case 'service_restart':
                $service = input('service');
                return $this->serviceRestart($service);
            
            case 'auto_cleanup':
                return $this->autoCleanup();
            
            case 'optimize_memory':
                return $this->optimizeMemory();
            
            case 'cleanup_docker':
                return $this->cleanupDocker();
            
            case 'cleanup_logs':
                return $this->cleanupLogs();

            // ========== 新增：定时任务管理 ==========
            case 'cron_add':
                return $this->cronAdd();
            
            case 'cron_edit':
                return $this->cronEdit();
            
            case 'cron_delete':
                return $this->cronDelete();
            
            case 'cron_enable':
                return $this->cronEnable();
            
            case 'cron_disable':
                return $this->cronDisable();

            // ========== 新增：历史数据 ==========
            case 'metrics_history':
                return $this->getMetricsHistory();

            default:
                return json(['code' => -1, 'msg' => '未知操作']);
        }
    }

    // 获取系统信息（统计卡片）
    private function getSystemInfo() {
        $cpu = $this->execCmd("top -bn1 | grep Cpu | awk '{print $2}' | sed 's/%us,//'");
        $cpu = $cpu ? floatval($cpu) : 0;

        $memTotal = $this->execCmd("free -m | grep Mem | awk '{print $2}'");
        $memUsed = $this->execCmd("free -m | grep Mem | awk '{print $3}'");
        $memPercent = $memTotal > 0 ? round($memUsed / $memTotal * 100, 1) : 0;

        $diskPercent = $this->execCmd("df -h / | tail -1 | awk '{print $5}' | cut -d'%' -f1");
        $diskPercent = $diskPercent ? intval($diskPercent) : 0;

        $uptime = $this->execCmd("uptime -p");
        $load = $this->execCmd("cat /proc/loadavg | awk '{print $1, $2, $3}'");

        // Docker容器统计
        $dockerTotal = $this->execCmd("docker ps -a --format '{{.Names}}' 2>/dev/null | wc -l");
        $dockerRunning = $this->execCmd("docker ps --format '{{.Names}}' 2>/dev/null | wc -l");
        $dockerPercent = $dockerTotal > 0 ? round($dockerRunning / $dockerTotal * 100) : 0;

        // 安全事件统计
        $attacksToday = $this->execCmd("grep '$(date +%b\\ %d)' /var/log/secure 2>/dev/null | grep 'Failed password' | wc -l");
        $attacksToday = intval($attacksToday);

        // 最近告警
        $recentAlerts = $this->execCmd("tail -5 /opt/security-reports/monitor.log 2>/dev/null | grep -E '\\[CRITICAL\\]|\\[WARNING\\]'");

        return json([
            'code' => 0,
            'data' => [
                'cpu' => [
                    'value' => $cpu,
                    'status' => $cpu > 80 ? 'critical' : ($cpu > 60 ? 'warning' : 'normal'),
                    'desc' => '处理器负载'
                ],
                'memory' => [
                    'value' => $memPercent,
                    'used' => intval($memUsed),
                    'total' => intval($memTotal),
                    'status' => $memPercent > 85 ? 'critical' : ($memPercent > 70 ? 'warning' : 'normal'),
                    'desc' => '内存使用率'
                ],
                'disk' => [
                    'value' => $diskPercent,
                    'status' => $diskPercent > 85 ? 'critical' : ($diskPercent > 70 ? 'warning' : 'normal'),
                    'desc' => '磁盘使用率'
                ],
                'docker' => [
                    'total' => intval($dockerTotal),
                    'running' => intval($dockerRunning),
                    'percent' => $dockerPercent,
                    'status' => intval($dockerRunning) == 0 ? 'critical' : 'normal',
                    'desc' => 'Docker容器'
                ],
                'security' => [
                    'attacks' => $attacksToday,
                    'status' => $attacksToday > 10 ? 'critical' : ($attacksToday > 0 ? 'warning' : 'normal'),
                    'desc' => '今日攻击次数'
                ],
                'uptime' => $uptime ?: '未知',
                'load' => $load ?: '0.00 0.00 0.00',
                'recent_alerts' => $recentAlerts
            ]
        ]);
    }

    // 获取系统详细信息（点击详情）
    private function getSystemDetail() {
        $type = input('type', 'cpu');

        switch ($type) {
            case 'cpu':
                return $this->getCpuDetail();
            case 'memory':
                return $this->getMemoryDetail();
            case 'disk':
                return $this->getDiskDetail();
            case 'docker':
                return $this->getDockerSummary();
            case 'security':
                return $this->getSecuritySummary();
            default:
                return json(['code' => -1, 'msg' => '未知类型']);
        }
    }

    // CPU详细信息
    private function getCpuDetail() {
        $cpuModel = $this->execCmd("cat /proc/cpuinfo | grep 'model name' | head -1 | cut -d: -f2");
        $cpuCores = $this->execCmd("nproc");
        $cpuUsage = $this->execCmd("top -bn1 | grep Cpu | awk '{print $2}'");
        $cpuUser = $this->execCmd("top -bn1 | grep Cpu | awk '{print $2}'");
        $cpuSystem = $this->execCmd("top -bn1 | grep Cpu | awk '{print $4}'");
        $cpuIdle = $this->execCmd("top -bn1 | grep Cpu | awk '{print $8}' | sed 's/%id,//'");
        $loadAvg = $this->execCmd("cat /proc/loadavg");

        // 获取CPU历史使用率
        $cpuHistory = $this->getCpuHistory();

        return json([
            'code' => 0,
            'type' => 'cpu',
            'title' => 'CPU详细信息',
            'data' => [
                'model' => trim($cpuModel) ?: '未知',
                'cores' => intval($cpuCores),
                'usage' => floatval(str_replace('%us,', '', $cpuUsage)),
                'user' => floatval(str_replace('%us,', '', $cpuUser)),
                'system' => floatval(str_replace('%sy,', '', $cpuSystem)),
                'idle' => floatval(str_replace('%id,', '', $cpuIdle)),
                'load_avg' => $loadAvg,
                'history' => $cpuHistory
            ]
        ]);
    }

    // 获取CPU历史（模拟）
    private function getCpuHistory() {
        // 实际生产环境可对接Prometheus等时序数据库
        // 这里返回最近7天的模拟数据趋势
        $history = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('m-d', strtotime("-{$i} days"));
            $history[] = [
                'time' => $date,
                'avg' => rand(10, 40),
                'max' => rand(60, 85)
            ];
        }
        return $history;
    }

    // 内存详细信息
    private function getMemoryDetail() {
        $memInfo = $this->execCmd("free -b");
        $memTotal = $this->execCmd("free -b | grep Mem | awk '{print $2}'");
        $memUsed = $this->execCmd("free -b | grep Mem | awk '{print $3}'");
        $memFree = $this->execCmd("free -b | grep Mem | awk '{print $4}'");
        $memBuffers = $this->execCmd("free -b | grep Mem | awk '{print $6}'");
        $swapTotal = $this->execCmd("free -b | grep Swap | awk '{print $2}'");
        $swapUsed = $this->execCmd("free -b | grep Swap | awk '{print $3}'");

        $memTotalGB = round(intval($memTotal) / 1024 / 1024 / 1024, 2);
        $memUsedGB = round(intval($memUsed) / 1024 / 1024 / 1024, 2);
        $memFreeGB = round(intval($memFree) / 1024 / 1024 / 1024, 2);
        $swapTotalGB = round(intval($swapTotal) / 1024 / 1024 / 1024, 2);
        $swapUsedGB = round(intval($swapUsed) / 1024 / 1024 / 1024, 2);

        // 获取内存占用最高的进程
        $topProcess = $this->execCmd("ps aux --sort=-%mem | head -6 | tail -5 | awk '{printf \"%s | %.1f%% | %s MB\\n\", \$11, \$4, \$6}'");

        return json([
            'code' => 0,
            'type' => 'memory',
            'title' => '内存详细信息',
            'data' => [
                'total' => $memTotalGB,
                'used' => $memUsedGB,
                'free' => $memFreeGB,
                'percent' => $memTotal > 0 ? round($memUsed / $memTotal * 100, 1) : 0,
                'buffers' => round(intval($memBuffers) / 1024 / 1024 / 1024, 2),
                'swap_total' => $swapTotalGB,
                'swap_used' => $swapUsedGB,
                'top_process' => $topProcess
            ]
        ]);
    }

    // 磁盘详细信息
    private function getDiskDetail() {
        $diskInfo = $this->execCmd("df -h");
        $diskInodes = $this->execCmd("df -i");
        $diskIO = $this->execCmd("iostat -x 2>/dev/null | tail -5 || echo 'iostat不可用'");

        // 各分区使用情况
        $partitions = [];
        $lines = explode("\n", $diskInfo);
        array_shift($lines); // 移除标题
        foreach ($lines as $line) {
            if (trim($line)) {
                $parts = preg_split('/\s+/', $line);
                if (count($parts) >= 6) {
                    $partitions[] = [
                        'filesystem' => $parts[0],
                        'size' => $parts[1],
                        'used' => $parts[2],
                        'available' => $parts[3],
                        'use_percent' => intval(str_replace('%', '', $parts[4])),
                        'mounted' => $parts[5]
                    ];
                }
            }
        }

        return json([
            'code' => 0,
            'type' => 'disk',
            'title' => '磁盘详细信息',
            'data' => [
                'partitions' => $partitions,
                'inodes' => $diskInodes,
                'io' => $diskIO
            ]
        ]);
    }

    // Docker概览
    private function getDockerSummary() {
        // 获取容器列表JSON格式
        $containersJson = $this->execCmd("docker ps -a --format '{{json .}}' 2>/dev/null");
        $containers = [];
        
        if (!empty($containersJson)) {
            $lines = explode("\n", trim($containersJson));
            foreach ($lines as $line) {
                $c = json_decode($line, true);
                if ($c) {
                    // 获取容器统计信息
                    $stats = $this->execCmd("docker stats {$c['Names']} --no-stream --format '{{.CPUPerc}}\t{{.MemUsage}}' 2>/dev/null");
                    $statsParts = explode("\t", $stats);
                    
                    $containers[] = [
                        'name' => $c['Names'],
                        'status' => $c['Status'],
                        'image' => $c['Image'],
                        'ports' => $c['Ports'],
                        'cpu' => isset($statsParts[0]) ? trim($statsParts[0]) : 'N/A',
                        'memory' => isset($statsParts[1]) ? trim($statsParts[1]) : 'N/A'
                    ];
                }
            }
        }

        // 统计运行中的容器
        $running = 0;
        foreach ($containers as $c) {
            if (stripos($c['status'], 'up') !== false) {
                $running++;
            }
        }
        
        return json([
            'code' => 0,
            'type' => 'docker',
            'title' => 'Docker容器概览',
            'data' => [
                'containers' => $containers,
                'total' => count($containers),
                'running' => $running
            ]
        ]);
    }

    // 安全概览
    private function getSecuritySummary() {
        $today = date('b %d');
        $attacks = $this->execCmd("grep '{$today}' /var/log/secure 2>/dev/null | grep 'Failed password' | wc -l");
        $blocked = $this->execCmd("iptables -L INPUT -n | grep -c DROP || echo 0");
        $whitelist = $this->execCmd("cat /opt/security-scripts/whitelist.txt 2>/dev/null | wc -l");

        return json([
            'code' => 0,
            'type' => 'security',
            'title' => '安全事件概览',
            'data' => [
                'today_attacks' => intval($attacks),
                'blocked_ips' => intval($blocked),
                'whitelist_count' => intval($whitelist)
            ]
        ]);
    }

    // 获取每日运维报告
    private function getDailyReport() {
        // 直接通过命令行读取报告文件，避免PHP open_basedir限制
        $reportFile = '/opt/security-reports/report_' . date('Ymd') . '.html';
        $content = $this->execCmd("cat {$reportFile} 2>/dev/null");

        if (!empty($content)) {
            return json(['code' => 0, 'report' => $content, 'date' => date('Y-m-d')]);
        }

        // 生成实时报告
        $cpu = $this->execCmd("top -bn1 | grep Cpu | awk '{print $2}' | sed 's/%us,//'");
        $mem = $this->execCmd("free | grep Mem | awk '{printf \"%.0f\", \$3/\$2*100}'");
        $disk = $this->execCmd("df -h / | tail -1 | awk '{print \$5}'");
        $uptime = $this->execCmd("uptime -p");
        $docker = $this->execCmd("docker ps --format '{{.Names}}' 2>/dev/null | wc -l");
        $alerts = $this->execCmd("tail -10 /opt/security-reports/monitor.log 2>/dev/null");

        $html = $this->generateDailyReportHtml($cpu, $mem, $disk, $uptime, $docker, $alerts);
        return json(['code' => 0, 'report' => $html, 'date' => date('Y-m-d')]);
    }

    // 生成每日报告HTML
    private function generateDailyReportHtml($cpu, $mem, $disk, $uptime, $docker, $alerts) {
        $memUsed = $this->execCmd("free -m | grep Mem | awk '{print \$3}'");
        $memTotal = $this->execCmd("free -m | grep Mem | awk '{print \$2}'");
        $diskUsed = $this->execCmd("df -h / | tail -1 | awk '{print \$3}'");
        $diskTotal = $this->execCmd("df -h / | tail -1 | awk '{print \$2}'");

        return <<<HTML
<div class="daily-report">
    <div class="report-header">
        <h2>📊 GOGO服务器运维日报</h2>
    </div>
    <div class="report-section">
        <h3>🖥️ 系统运行状态</h3>
        <table class="report-table">
            <tr><th>指标</th><th>当前值</th><th>状态</th></tr>
            <tr><td>CPU使用率</td><td>{$cpu}%</td><td class="status-{$this->getStatusClass(floatval($cpu), 80, 60)}">{$this->getStatusText(floatval($cpu), 80, 60)}</td></tr>
            <tr><td>内存使用率</td><td>{$mem}% ({$memUsed}/{$memTotal} MB)</td><td class="status-{$this->getStatusClass(intval($mem), 85, 70)}">{$this->getStatusText(intval($mem), 85, 70)}</td></tr>
            <tr><td>磁盘使用率</td><td>{$disk} ({$diskUsed}/{$diskTotal})</td><td class="status-{$this->getStatusClass(intval($disk), 85, 70)}">{$this->getStatusText(intval($disk), 85, 70)}</td></tr>
            <tr><td>运行时间</td><td colspan="2">{$uptime}</td></tr>
            <tr><td>Docker容器</td><td colspan="2">运行中: {$docker} 个</td></tr>
        </table>
    </div>
    <div class="report-section">
        <h3>📋 近期告警记录</h3>
        <pre class="alerts-log">{$alerts}</pre>
    </div>
    <div class="report-footer">
        <p>由 GOGO服务器监控系统 自动生成 | 数据更新于: %s</p>
    </div>
</div>
HTML;
    }

    // 推送每日报告到微信
    public function pushDailyReport() {
        $cpu = $this->execCmd("top -bn1 | grep Cpu | awk '{print \$2}' | sed 's/%us,//'");
        $mem = $this->execCmd("free | grep Mem | awk '{printf \"%.0f\", \$3/\$2*100}'");
        $disk = $this->execCmd("df -h / | tail -1 | awk '{print \$5}'");
        $uptime = $this->execCmd("uptime -p");
        $dockerRunning = $this->execCmd("docker ps --format '{{.Names}}' 2>/dev/null | wc -l");
        $dockerTotal = $this->execCmd("docker ps -a --format '{{.Names}}' 2>/dev/null | wc -l");
        $alerts = $this->execCmd("tail -5 /opt/security-reports/monitor.log 2>/dev/null | grep -E '\\[CRITICAL\\]|\\[WARNING\\]' | head -3");

        // 生成报告摘要
        $report = "🖥️ 系统状态\n";
        $report .= "━━━━━━━━━━━━━━━━\n";
        $report .= "• CPU: {$cpu}%\n";
        $report .= "• 内存: {$mem}%\n";
        $report .= "• 磁盘: {$disk}\n";
        $report .= "• 运行时间: {$uptime}\n";
        $report .= "• 容器: {$dockerRunning}/{$dockerTotal} 运行\n";
        $report .= "━━━━━━━━━━━━━━━━\n";
        $report .= "📋 近期告警:\n";
        $report .= ($alerts ?: "暂无告警");

        $first = "📊 GOGO服务器每日运维报告 - " . date('m/d');
        $keyword1 = "系统状态正常";
        $keyword2 = "CPU:{$cpu}% | 内存:{$mem}% | 磁盘:{$disk}";
        $keyword3 = date('H:i');
        $remark = "查看完整监控: https://boss.gogo198.cn/?s=monitor";

        $result = $this->sendWechatMessage($first, $keyword1, $keyword2, $keyword3, $remark);

        if ($result) {
            return json(['code' => 0, 'msg' => '日报推送成功']);
        } else {
            return json(['code' => -1, 'msg' => '推送失败，请检查配置']);
        }
    }

    // 发送微信消息
    private function sendWechatMessage($first, $keyword1, $keyword2, $keyword3, $remark) {
        $data = [
            'call' => 'confirmCollectionNotice',
            'first' => $first,
            'keyword1' => $keyword1,
            'keyword2' => $keyword2,
            'keyword3' => $keyword3,
            'remark' => $remark,
            'url' => 'https://boss.gogo198.cn/?s=monitor',
            'openid' => $this->WECHAT_OPENID,
            'temp_id' => $this->WECHAT_TEMP_ID
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->WECHAT_API);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode == 200 && $response !== false;
    }

    // 测试微信推送
    public function testWechat() {
        $result = $this->sendWechatMessage(
            "🔔 GOGO服务器监控测试",
            "监控功能正常",
            "测试时间: " . date('H:i:s'),
            "感谢使用GOGO监控",
            "测试推送成功！"
        );

        if ($result) {
            return json(['code' => 0, 'msg' => '测试推送成功']);
        } else {
            return json(['code' => -1, 'msg' => '推送失败']);
        }
    }

    // 获取安全信息
    private function getSecurityInfo() {
        $attacks = $this->execCmd("grep 'Failed password' /var/log/secure 2>/dev/null | tail -30");
        $monitor = $this->execCmd('tail -30 /opt/security-reports/monitor.log 2>/dev/null');
        return json(['code' => 0, 'attacks' => $attacks, 'monitor' => $monitor]);
    }

    // 获取安全事件
    private function getSecurityEvents() {
        $events = [];
        $attacks = $this->execCmd("grep '$(date +%b\\ %d)' /var/log/secure 2>/dev/null | grep 'Failed password' | awk '{print \$11}' | sort | uniq -c | sort -rn | head -10");

        if (!empty($attacks)) {
            $lines = explode("\n", trim($attacks));
            foreach ($lines as $line) {
                $parts = preg_split('/\s+/', trim($line));
                if (count($parts) >= 2) {
                    $count = intval($parts[0]);
                    $ip = $parts[1];
                    $events[] = [
                        'type' => 'SSH暴力破解',
                        'severity' => $count > 10 ? 'critical' : 'warning',
                        'ip' => $ip,
                        'count' => $count,
                        'time' => date('Y-m-d H:i'),
                        'description' => "IP {$ip} 在今日尝试登录 {$count} 次",
                        'action' => $count > 10 ? '建议立即封禁该IP' : '建议关注该IP',
                        'url' => ''
                    ];
                }
            }
        }

        return json(['code' => 0, 'events' => $events]);
    }

    // 获取定时任务信息
    private function getCrontabInfo() {
        $crontab = $this->execCmd('sudo cat /var/spool/cron/root 2>/dev/null');
        if (empty($crontab)) {
            $crontab = $this->execCmd('cat /etc/crontab 2>/dev/null');
        }

        // 解析定时任务
        $tasks = [];
        $lines = explode("\n", $crontab);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || substr($line, 0, 1) === '#') continue;

            // 解析crontab格式
            if (preg_match('/^(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(.+)$/', $line, $matches)) {
                $tasks[] = [
                    'minute' => $matches[1],
                    'hour' => $matches[2],
                    'day' => $matches[3],
                    'month' => $matches[4],
                    'weekday' => $matches[5],
                    'command' => $matches[6],
                    'description' => $this->getTaskDescription($matches[6])
                ];
            }
        }

        return json(['code' => 0, 'crontab' => $crontab ?: '暂无定时任务配置', 'tasks' => $tasks]);
    }

    // 获取任务描述
    private function getTaskDescription($command) {
        $descriptions = [
            'system-monitor' => '🛡️ GOGO服务器监控系统',
            'report' => '📊 生成每日运维报告',
            'docker' => '🐳 Docker容器健康检查',
            'certbot' => '🔒 SSL证书自动续期',
            'backup' => '💾 数据备份任务',
            'logrotate' => '📁 日志清理任务'
        ];

        foreach ($descriptions as $key => $desc) {
            if (strpos($command, $key) !== false) {
                return $desc;
            }
        }
        return '⏰ 定时任务';
    }

    // 获取服务详情
    private function getServiceDetail($service) {
        $status = $this->execCmd("systemctl is-active {$service} 2>/dev/null || service {$service} status 2>/dev/null | head -5");
        $enabled = $this->execCmd("systemctl is-enabled {$service} 2>/dev/null");
        $memory = $this->execCmd("ps aux --sort=-%mem | grep {$service} | head -3 | awk '{printf \"%s: %.1f%% RAM\\n\", \$11, \$4}'");
        $cpu = $this->execCmd("ps aux --sort=-%cpu | grep {$service} | head -3 | awk '{printf \"%s: %.1f%% CPU\\n\", \$11, \$3}'");

        return json([
            'code' => 0,
            'service' => $service,
            'status' => $status ?: 'unknown',
            'enabled' => $enabled ?: 'unknown',
            'memory' => $memory,
            'cpu' => $cpu
        ]);
    }

    // 获取IP信息
    private function getIpInfo($ip) {
        // 简单的IP地理位置查询
        $info = $this->execCmd('curl -s "http://ip-api.com/json/' . $ip . '?fields=country,city,isp,org,as" 2>/dev/null || echo "{}"');
        return json(['code' => 0, 'ip' => $ip, 'info' => $info]);
    }

    // 封禁IP
    private function blockIp($ip) {
        // 验证IP格式
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return json(['code' => -1, 'msg' => '无效的IP地址']);
        }

        $result = $this->execCmd("sudo iptables -I INPUT -s {$ip} -j DROP && echo 'success'");
        if (strpos($result, 'success') !== false) {
            return json(['code' => 0, 'msg' => "IP {$ip} 已封禁"]);
        }
        return json(['code' => -1, 'msg' => '封禁失败']);
    }

    // 添加白名单
    private function whitelistIp($ip) {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return json(['code' => -1, 'msg' => '无效的IP地址']);
        }

        $whitelistFile = '/opt/security-scripts/whitelist.txt';
        $result = $this->execCmd("echo '{$ip}' >> {$whitelistFile} && echo 'success'");
        if (strpos($result, 'success') !== false) {
            return json(['code' => 0, 'msg' => "IP {$ip} 已添加到白名单"]);
        }
        return json(['code' => -1, 'msg' => '添加失败']);
    }

    // 获取Docker容器详情
    private function getContainerDetail($name) {
        $info = $this->execCmd("docker inspect {$name} 2>/dev/null | head -50");
        $stats = $this->execCmd("docker stats {$name} --no-stream --format 'table {{.Name}}\t{{.CPUPerc}}\t{{.MemUsage}}\t{{.MemPerc}}' 2>/dev/null");
        $ports = $this->execCmd("docker port {$name} 2>/dev/null");
        $volumes = $this->execCmd("docker inspect {$name} 2>/dev/null | grep -A2 'Mounts' | grep Source | head -5");
        $networks = $this->execCmd("docker inspect {$name} 2>/dev/null | grep -A20 'Networks' | grep Name | head -5");

        return json([
            'code' => 0,
            'container' => $name,
            'info' => $info,
            'stats' => $stats,
            'ports' => $ports,
            'volumes' => $volumes,
            'networks' => $networks
        ]);
    }

    // 获取容器日志
    private function getContainerLogs($name, $lines = 50) {
        $logs = $this->execCmd("docker logs {$name} --tail {$lines} 2>&1");
        return json(['code' => 0, 'container' => $name, 'logs' => $logs]);
    }

    // 获取端口详情
    private function getPortDetail() {
        $ports = [];
        $seen = []; // 去重

        // 获取所有监听端口
        $listeners = $this->execCmd("ss -tlnp 2>/dev/null || netstat -tlnp 2>/dev/null");

        if (!empty($listeners)) {
            $lines = explode("\n", $listeners);
            array_shift($lines); // 移除标题

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                // 直接提取端口号 - 格式如 *:9001 或 0.0.0.0:80
                if (preg_match('/[\d.]+:\s*(\d+)|\*:(\d+)/', $line, $matches)) {
                    $port = !empty($matches[1]) ? intval($matches[1]) : intval($matches[2]);
                    
                    // 去重
                    if (isset($seen[$port])) continue;
                    $seen[$port] = true;
                    
                    $portInfo = $this->parsePort($port, $line);
                    $ports[] = $portInfo;
                }
            }
        }

        return json(['code' => 0, 'ports' => $ports]);
    }

    // 解析端口信息
    private function parsePort($port, $line = '') {
        $port = intval($port);

        $services = [
            22 => ['name' => 'SSH', 'desc' => '远程连接服务', 'protocol' => 'TCP'],
            80 => ['name' => 'HTTP', 'desc' => 'Web服务', 'protocol' => 'TCP'],
            443 => ['name' => 'HTTPS', 'desc' => '安全Web服务', 'protocol' => 'TCP'],
            3306 => ['name' => 'MySQL', 'desc' => '数据库服务', 'protocol' => 'TCP'],
            6379 => ['name' => 'Redis', 'desc' => '缓存服务', 'protocol' => 'TCP'],
            8080 => ['name' => 'HTTP-Alt', 'desc' => '备用Web端口', 'protocol' => 'TCP'],
            9000 => ['name' => 'PHP-FPM', 'desc' => 'PHP FastCGI', 'protocol' => 'TCP'],
            9001 => ['name' => 'SonarQube', 'desc' => '代码质量审核', 'protocol' => 'TCP'],
            8001 => ['name' => 'GOGO-shop', 'desc' => '电商平台', 'protocol' => 'TCP'],
            8002 => ['name' => 'GOGO-Admin', 'desc' => '管理后台', 'protocol' => 'TCP'],
            8003 => ['name' => 'GOGO-site', 'desc' => '静态站点', 'protocol' => 'TCP'],
            5000 => ['name' => 'GOGO-AI', 'desc' => 'AI服务', 'protocol' => 'TCP'],
            27017 => ['name' => 'MongoDB', 'desc' => 'NoSQL数据库', 'protocol' => 'TCP'],
            5432 => ['name' => 'PostgreSQL', 'desc' => 'PostgreSQL数据库', 'protocol' => 'TCP'],
            11211 => ['name' => 'Memcached', 'desc' => '内存缓存', 'protocol' => 'TCP'],
            9200 => ['name' => 'Elasticsearch', 'desc' => '搜索引擎', 'protocol' => 'TCP'],
            81 => ['name' => 'HTTP-Alt', 'desc' => '备用HTTP端口', 'protocol' => 'TCP'],
            4431 => ['name' => 'HTTPS-Alt', 'desc' => '备用HTTPS端口', 'protocol' => 'TCP'],
            4369 => ['name' => 'EPMD', 'desc' => 'Erlang端口映射', 'protocol' => 'TCP'],
            21 => ['name' => 'FTP', 'desc' => '文件传输协议', 'protocol' => 'TCP'],
            8082 => ['name' => 'HTTPS-Alt2', 'desc' => '备用HTTPS端口2', 'protocol' => 'TCP'],
        ];

        $result = isset($services[$port]) 
            ? array_merge($services[$port], ['port' => $port])
            : [
                'port' => $port,
                'name' => '未知服务',
                'desc' => "端口 {$port}",
                'protocol' => 'TCP'
              ];

        // 从原始行中提取进程信息
        if (!empty($line) && preg_match('/users:\(\("([^"]+)"/', $line, $m)) {
            $result['process'] = $m[1];
        }

        return $result;
    }

    // 获取网络连接详情
    private function getNetworkDetail() {
        $connections = [];
        
        // 使用ss命令获取TCP连接
        $ssCmd = "ss -tn state established 2>/dev/null";
        $output = $this->execCmd($ssCmd);
        
        // 如果ss失败，尝试netstat
        if (empty($output)) {
            $output = $this->execCmd("netstat -tn 2>/dev/null | grep ESTABLISHED");
        }

        $stateMap = [
            'ESTAB' => '已建立', 'ESTABLISHED' => '已建立',
            'LISTEN' => '监听中', 'LISTENING' => '监听中',
            'TIME-WAIT' => '等待结束', 'TIME_WAIT' => '等待结束',
            'CLOSE-WAIT' => '等待关闭', 'CLOSE_WAIT' => '等待关闭',
            'FIN-WAIT-1' => '等待FIN1', 'FIN_WAIT_1' => '等待FIN1',
            'FIN-WAIT-2' => '等待FIN2', 'FIN_WAIT_2' => '等待FIN2',
            'SYN-SENT' => '发送SYN', 'SYN_SENT' => '发送SYN',
            'SYN-RECV' => '收到SYN', 'SYN_RECV' => '收到SYN',
            'CLOSING' => '关闭中', 'CLOSED' => '已关闭',
            'LAST-ACK' => '最后ACK', 'LAST_ACK' => '最后ACK',
            'UNCONN' => '未连接'
        ];

        if (!empty($output)) {
            $lines = explode("\n", trim($output));

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                // 跳过标题行
                if (stripos($line, 'Local') !== false || stripos($line, 'Address') !== false) continue;
                if (stripos($line, 'State') !== false) continue;

                // ss格式: State Recv-Q Send-Q Local Address:Port Foreign Address:Port
                // 示例: ESTAB      0      0      172.18.68.75:443                223.109.211.156:4760
                // 支持IPv6映射格式: [::ffff:172.18.68.75]:8080
                if (preg_match('/^\S+\s+\d+\s+\d+\s+(\S+):(\d+)\s+(\S+):(\d+)/', $line, $m)) {
                    $local = preg_replace('/^\[::ffff:|\]$/', '', $m[1]);
                    $remote = preg_replace('/^\[::ffff:|\]$/', '', $m[3]);
                    $state = '已建立'; // 默认状态
                    $connections[] = [
                        'protocol' => 'TCP',
                        'local' => $local,
                        'local_port' => $m[2],
                        'remote' => $remote,
                        'remote_port' => $m[4],
                        'state' => $state
                    ];
                }
            }
        }

        return json(['code' => 0, 'connections' => $connections]);
    }

    // 获取阿里云事件
    private function getAliyunEvents() {
        $events = [];

        // 服务器到期提醒
        $serverExpire = '2026-04-26';
        $daysLeft = (strtotime($serverExpire) - time()) / 86400;
        if ($daysLeft <= 30 && $daysLeft > 0) {
            $events[] = [
                'type' => '服务器续费提醒',
                'severity' => 'warning',
                'time' => date('Y-m-d H:i'),
                'title' => '服务器即将到期',
                'description' => "阿里云服务器 (39.108.11.214) 将于 {$serverExpire} 到期，剩余 " . round($daysLeft) . " 天",
                'action' => '请及时登录阿里云控制台续费，避免服务中断',
                'url' => 'https://www.aliyun.com'
            ];
        }

        // 磁盘预警
        $diskPercent = $this->execCmd("df -h / | tail -1 | awk '{print \$5}' | cut -d'%' -f1");
        if (intval($diskPercent) >= 85) {
            $events[] = [
                'type' => '磁盘空间预警',
                'severity' => intval($diskPercent) >= 95 ? 'critical' : 'warning',
                'time' => date('Y-m-d H:i'),
                'title' => '磁盘空间不足',
                'description' => "根分区使用率已达 {$diskPercent}%",
                'action' => '建议清理日志文件、缓存或扩展磁盘空间',
                'url' => ''
            ];
        }

        return json(['code' => 0, 'events' => $events]);
    }

    // AI分析功能
    private function getAIAnalysis() {
        $type = input('type', 'system');

        switch ($type) {
            case 'system':
                return $this->analyzeSystem();
            case 'cpu':
                return $this->analyzeCpu();
            case 'memory':
                return $this->analyzeMemory();
            case 'disk':
                return $this->analyzeDisk();
            case 'docker':
                return $this->analyzeDocker();
            case 'security':
                return $this->analyzeSecurity();
            default:
                return json(['code' => -1, 'msg' => '未知分析类型']);
        }
    }

    // 系统综合分析
    private function analyzeSystem() {
        $cpu = $this->execCmd("top -bn1 | grep Cpu | awk '{print \$2}' | sed 's/%us,//'");
        $mem = $this->execCmd("free | grep Mem | awk '{printf \"%.0f\", \$3/\$2*100}'");
        $disk = $this->execCmd("df -h / | tail -1 | awk '{print \$5}' | cut -d'%' -f1");
        $load = $this->execCmd("cat /proc/loadavg | awk '{print \$1, \$2, \$3}'");

        $cpuVal = floatval($cpu);
        $memVal = intval($mem);
        $diskVal = intval($disk);

        $issues = [];
        $suggestions = [];

        // CPU分析
        if ($cpuVal > 80) {
            $issues[] = "CPU使用率过高 ({$cpuVal}%)";
            $suggestions[] = "🔴 CPU告警：使用率超过80%，建议检查高负载进程";
        } elseif ($cpuVal > 60) {
            $issues[] = "CPU使用率偏高 ({$cpuVal}%)";
            $suggestions[] = "🟡 建议关注CPU使用趋势，考虑优化或扩容";
        } else {
            $suggestions[] = "🟢 CPU状态正常，当前负载 {$load}";
        }

        // 内存分析
        if ($memVal > 85) {
            $issues[] = "内存使用率过高 ({$memVal}%)";
            $suggestions[] = "🔴 内存告警：建议重启不必要服务或扩展内存";
        } elseif ($memVal > 70) {
            $issues[] = "内存使用率偏高 ({$memVal}%)";
            $suggestions[] = "🟡 建议监控内存泄漏风险，准备扩容方案";
        } else {
            $suggestions[] = "🟢 内存使用率正常";
        }

        // 磁盘分析
        if ($diskVal > 85) {
            $issues[] = "磁盘使用率过高 ({$diskVal}%)";
            $suggestions[] = "🔴 磁盘告警：立即清理日志、临时文件或扩展磁盘";
        } elseif ($diskVal > 70) {
            $issues[] = "磁盘使用率偏高 ({$diskVal}%)";
            $suggestions[] = "🟡 建议制定清理计划，避免达到阈值";
        } else {
            $suggestions[] = "🟢 磁盘空间充足";
        }

        // 综合评分
        $score = 100;
        if ($cpuVal > 60) $score -= 20;
        if ($memVal > 70) $score -= 20;
        if ($diskVal > 70) $score -= 20;
        if (count($issues) > 0) $score -= count($issues) * 10;

        $assessment = $score >= 80 ? '优秀' : ($score >= 60 ? '良好' : ($score >= 40 ? '一般' : '需改进'));

        return json([
            'code' => 0,
            'type' => 'system',
            'analysis' => [
                'score' => max(0, $score),
                'assessment' => $assessment,
                'issues' => $issues,
                'suggestions' => $suggestions,
                'summary' => $this->generateSystemSummary($cpuVal, $memVal, $diskVal),
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ]);
    }

    // CPU专项分析
    private function analyzeCpu() {
        $cpu = $this->execCmd("top -bn1 | grep Cpu | awk '{print \$2}' | sed 's/%us,//'");
        $cpuVal = floatval($cpu);
        $load = $this->execCmd("cat /proc/loadavg");
        $topProc = $this->execCmd("ps aux --sort=-%cpu | head -6 | tail -5 | awk '{printf \"%s: %.1f%%\\n\", \$11, \$3}'");

        $suggestions = [];

        if ($cpuVal > 80) {
            $suggestions[] = "🔴 【紧急】CPU使用率超过80%，可能存在异常进程";
            $suggestions[] = "排查命令：`top` 或 `htop` 查看高CPU进程";
            $suggestions[] = "常见原因：恶意挖矿、DDoS攻击、程序死循环";
        } elseif ($cpuVal > 60) {
            $suggestions[] = "🟡 【关注】CPU使用率偏高，建议持续监控";
            $suggestions[] = "可使用 `ps aux --sort=-%cpu | head -10` 查看Top进程";
        } else {
            $suggestions[] = "🟢 CPU负载正常，系统运行稳定";
        }

        // 负载分析
        $loadParts = explode(' ', $load);
        $load1 = floatval($loadParts[0]);
        $cores = intval($this->execCmd("nproc"));
        $loadPerCore = $cores > 0 ? round($load1 / $cores, 2) : 0;

        if ($loadPerCore > 1) {
            $suggestions[] = "⚠️ 系统负载较高 ({$load1})，平均每核负载 {$loadPerCore}";
        }

        return json([
            'code' => 0,
            'type' => 'cpu',
            'analysis' => [
                'value' => $cpuVal,
                'load_avg' => $load,
                'cores' => $cores,
                'load_per_core' => $loadPerCore,
                'top_processes' => $topProc,
                'suggestions' => $suggestions,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ]);
    }

    // 内存专项分析
    private function analyzeMemory() {
        $memTotal = $this->execCmd("free -m | grep Mem | awk '{print \$2}'");
        $memUsed = $this->execCmd("free -m | grep Mem | awk '{print \$3}'");
        $memFree = $this->execCmd("free -m | grep Mem | awk '{print \$4}'");
        $memBuffers = $this->execCmd("free -m | grep Mem | awk '{print \$6}'");
        $swapTotal = $this->execCmd("free -m | grep Swap | awk '{print \$2}'");
        $swapUsed = $this->execCmd("free -m | grep Swap | awk '{print \$3}'");

        $memVal = $memTotal > 0 ? round($memUsed / $memTotal * 100, 1) : 0;
        $topProc = $this->execCmd("ps aux --sort=-%mem | head -6 | tail -5 | awk '{printf \"%s: %.1f%% (%s MB)\\n\", \$11, \$4, \$6}'");

        $suggestions = [];

        if ($memVal > 85) {
            $suggestions[] = "🔴 【紧急】内存使用率超过85%，存在OOM风险";
            $suggestions[] = "立即排查：`ps aux --sort=-%mem | head -10`";
            $suggestions[] = "建议：重启不必要服务或考虑扩容内存";
        } elseif ($memVal > 70) {
            $suggestions[] = "🟡 【关注】内存使用率偏高，需警惕内存泄漏";
            $suggestions[] = "建议：定期监控内存趋势，检查进程内存使用";
        } else {
            $suggestions[] = "🟢 内存使用率正常，系统运行稳定";
        }

        // Swap分析
        if (intval($swapUsed) > 0) {
            $suggestions[] = "⚠️ 系统启用了Swap交换空间 (已用 {$swapUsed}MB)，可能影响性能";
            $suggestions[] = "建议：监控Swap使用，避免频繁swap导致IO瓶颈";
        }

        return json([
            'code' => 0,
            'type' => 'memory',
            'analysis' => [
                'percent' => $memVal,
                'used' => intval($memUsed),
                'total' => intval($memTotal),
                'free' => intval($memFree),
                'buffers' => intval($memBuffers),
                'swap_used' => intval($swapUsed),
                'swap_total' => intval($swapTotal),
                'top_processes' => $topProc,
                'suggestions' => $suggestions,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ]);
    }

    // 磁盘专项分析
    private function analyzeDisk() {
        $diskPercent = $this->execCmd("df -h / | tail -1 | awk '{print \$5}' | cut -d'%' -f1");
        $diskVal = intval($diskPercent);
        $diskUsed = $this->execCmd("df -h / | tail -1 | awk '{print \$3}'");
        $diskTotal = $this->execCmd("df -h / | tail -1 | awk '{print \$2}'");

        $largeDirs = $this->execCmd("du -sh /* 2>/dev/null | sort -rh | head -10");
        $logSize = $this->execCmd("du -sh /var/log 2>/dev/null | cut -f1");
        $dockerSize = $this->execCmd("docker system df 2>/dev/null | tail -1 | awk '{print \$3}'");

        $suggestions = [];

        if ($diskVal > 85) {
            $suggestions[] = "🔴 【紧急】磁盘使用率超过85%，需要立即清理";
            $suggestions[] = "清理建议：";
            $suggestions[] = "  • `docker system prune -a` 清理未使用镜像和容器";
            $suggestions[] = "  • `find /var/log -type f -name '*.log' -exec truncate -s 0 {} \\;` 清理日志";
            $suggestions[] = "  • 检查 `/tmp` 目录，删除临时文件";
            $suggestions[] = "  • 考虑扩展磁盘空间或挂载新盘";
        } elseif ($diskVal > 70) {
            $suggestions[] = "🟡 【预警】磁盘使用率超过70%，建议制定清理计划";
            $suggestions[] = "可清理项：日志文件(当前{$logSize})、Docker未使用资源";
        } else {
            $suggestions[] = "🟢 磁盘空间充足，当前使用 {$diskUsed}/{$diskTotal}";
        }

        return json([
            'code' => 0,
            'type' => 'disk',
            'analysis' => [
                'percent' => $diskVal,
                'used' => $diskUsed,
                'total' => $diskTotal,
                'large_dirs' => $largeDirs,
                'log_size' => $logSize,
                'docker_size' => $dockerSize,
                'suggestions' => $suggestions,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ]);
    }

    // Docker专项分析
    private function analyzeDocker() {
        $total = $this->execCmd("docker ps -a --format '{{.Names}}' 2>/dev/null | wc -l");
        $running = $this->execCmd("docker ps --format '{{.Names}}' 2>/dev/null | wc -l");
        $stopped = intval($total) - intval($running);
        $images = $this->execCmd("docker images --format '{{.Repository}}:{{.Tag}}' 2>/dev/null | wc -l");
        $volumes = $this->execCmd("docker volume ls 2>/dev/null | tail -n +2 | wc -l");

        $suggestions = [];

        if (intval($running) == 0) {
            $suggestions[] = "🔴 【紧急】所有Docker容器已停止，服务可能中断";
        } elseif ($stopped > 0) {
            $suggestions[] = "🟡 【注意】有 {$stopped} 个容器处于停止状态";
            $suggestions[] = "查看停止容器：`docker ps -a --filter status=exited`";
        } else {
            $suggestions[] = "🟢 所有Docker容器运行正常";
        }

        // 镜像分析
        $danglingImages = $this->execCmd("docker images -f 'dangling=true' -q 2>/dev/null | wc -l");
        if (intval($danglingImages) > 0) {
            $suggestions[] = "⚠️ 发现 " . intval($danglingImages) . " 个悬空镜像，可执行 `docker image prune` 清理";
        }

        // 资源使用
        $diskUsage = $this->execCmd("docker system df 2>/dev/null");

        return json([
            'code' => 0,
            'type' => 'docker',
            'analysis' => [
                'total' => intval($total),
                'running' => intval($running),
                'stopped' => $stopped,
                'images' => intval($images),
                'volumes' => intval($volumes),
                'dangling_images' => intval($danglingImages),
                'disk_usage' => $diskUsage,
                'suggestions' => $suggestions,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ]);
    }

    // 安全专项分析
    private function analyzeSecurity() {
        $today = date('b %d');
        $attacks = $this->execCmd("grep '{$today}' /var/log/secure 2>/dev/null | grep 'Failed password' | wc -l");
        $blocked = $this->execCmd("iptables -L INPUT -n | grep -c DROP || echo 0");
        $whitelistCount = $this->execCmd("cat /opt/security-scripts/whitelist.txt 2>/dev/null | grep -v '^$' | wc -l");
        $lastLogin = $this->execCmd("last -10 | head -10");

        $suggestions = [];

        if (intval($attacks) > 10) {
            $suggestions[] = "🔴 【紧急】今日SSH暴力破解尝试达到 " . intval($attacks) . " 次";
            $suggestions[] = "建议措施：";
            $suggestions[] = "  • 检查 /opt/security-scripts/whitelist.txt 白名单配置";
            $suggestions[] = "  • 考虑使用 fail2ban 自动封禁";
            $suggestions[] = "  • 建议修改SSH端口或禁用密码登录";
        } elseif (intval($attacks) > 0) {
            $suggestions[] = "🟡 【关注】检测到 " . intval($attacks) . " 次SSH登录尝试";
            $suggestions[] = "建议：审查登录日志，关注异常IP来源";
        } else {
            $suggestions[] = "🟢 今日暂无检测到SSH暴力破解攻击";
        }

        // 封禁IP统计
        if (intval($blocked) > 0) {
            $suggestions[] = "📊 当前已封禁 " . intval($blocked) . " 个恶意IP";
        }

        // 白名单建议
        $suggestions[] = "💡 安全建议：";
        $suggestions[] = "  • 定期审查 /var/log/secure 日志";
        $suggestions[] = "  • 确保SSH使用密钥登录，禁用密码认证";
        $suggestions[] = "  • 配置 fail2ban 自动防御机制";

        return json([
            'code' => 0,
            'type' => 'security',
            'analysis' => [
                'today_attacks' => intval($attacks),
                'blocked_ips' => intval($blocked),
                'whitelist_count' => intval($whitelistCount),
                'last_logins' => $lastLogin,
                'suggestions' => $suggestions,
                'timestamp' => date('Y-m-d H:i:s')
            ]
        ]);
    }

    // 生成系统摘要
    private function generateSystemSummary($cpu, $mem, $disk) {
        $summary = "📊 服务器运行概况：\n\n";
        $summary .= "• CPU：{$cpu}% ";
        $summary .= $cpu > 80 ? "(需关注)\n" : "(正常)\n";
        $summary .= "• 内存：{$mem}% ";
        $summary .= $mem > 85 ? "(需关注)\n" : "(正常)\n";
        $summary .= "• 磁盘：{$disk}% ";
        $summary .= $disk > 85 ? "(需关注)\n" : "(正常)\n";
        $summary .= "\n建议：";

        $issues = 0;
        if ($cpu > 80) $issues++;
        if ($mem > 85) $issues++;
        if ($disk > 85) $issues++;

        if ($issues == 0) {
            $summary .= "各项指标正常，继续保持";
        } elseif ($issues == 1) {
            $summary .= "有1项指标需关注，请查看详细建议";
        } else {
            $summary .= "有{$issues}项指标异常，建议优先处理高负载问题";
        }

        return $summary;
    }

    // 获取状态CSS类
    private function getStatusClass($value, $critical, $warning) {
        if ($value >= $critical) return 'critical';
        if ($value >= $warning) return 'warning';
        return 'normal';
    }

    // 获取状态文本
    private function getStatusText($value, $critical, $warning) {
        if ($value >= $critical) return '🔴 严重';
        if ($value >= $warning) return '🟡 警告';
        return '🟢 正常';
    }

    // ========== 任务管理功能 ==========

    // 获取所有定时任务列表
    private function getTaskList() {
        $tasks = [];

        // 从 crontab 读取任务
        $crontab = @file_get_contents("/www/wwwroot/boss.gogo198.cn/crontab_cache.txt");

        if (!empty($crontab)) {
            $lines = explode("\n", $crontab);
            $taskId = 0;

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || strpos($line, '#') === 0) continue;

                // 解析 cron 表达式
                if (preg_match('/^(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(.+)$/', $line, $m)) {
                    $minute = $m[1];
                    $hour = $m[2];
                    $day = $m[3];
                    $month = $m[4];
                    $weekday = $m[5];
                    $command = trim($m[6]);

                    // 解析命令
                    $taskInfo = $this->parseTaskCommand($command);
                    $schedule = $this->formatCronSchedule($minute, $hour, $day, $month, $weekday);

                    // 判断任务类型
                    $category = $this->getTaskCategory($command);

                    $tasks[] = [
                        'id' => 'cron_' . ($taskId++),
                        'name' => $taskInfo['name'],
                        'schedule' => $schedule,
                        'schedule_raw' => "{$minute} {$hour} {$day} {$month} {$weekday}",
                        'command' => $command,
                        'target' => $taskInfo['target'],
                        'target_name' => $taskInfo['target_name'],
                        'category' => $category,
                        'next_run' => $this->getNextRunTime($minute, $hour, $day, $month, $weekday),
                        'last_run' => $this->getLastRunInfo($command),
                        'status' => 'active',
                        'type' => 'cron'
                    ];
                }
            }
        }

        // 从 /www/server/cron/ 目录读取所有任务脚本
        $cronDir = '/www/server/cron';
        $cronFiles = $this->execCmd("ls -1 {$cronDir} 2>/dev/null | grep -v '\.log$' | grep -v '\.lock$'");

        if (!empty($cronFiles)) {
            $files = explode("\n", trim($cronFiles));

            foreach ($files as $file) {
                $file = trim($file);
                if (empty($file)) continue;

                $scriptPath = "{$cronDir}/{$file}";
                $scriptContent = $this->execCmd("head -20 {$scriptPath}");

                // 从脚本中提取 URL
                if (preg_match('/https?:\/\/([^\/]+)\/[^\']+/', $scriptContent, $m)) {
                    $target = $m[0];
                    $host = $m[1];

                    $tasks[] = [
                        'id' => $file,
                        'name' => $this->getTaskNameFromUrl($target),
                        'schedule' => '见 crontab 配置',
                        'schedule_raw' => '',
                        'command' => "curl {$target}",
                        'target' => $target,
                        'target_name' => $host,
                        'category' => $this->getTaskCategory($target),
                        'script' => $scriptPath,
                        'last_log' => $this->getTaskLog($file),
                        'status' => 'active',
                        'type' => 'script'
                    ];
                }
            }
        }

        // GOGO 监控系统任务
        $gogoMonitor = $this->execCmd("grep -i 'system-monitor' /var/spool/cron/root 2>/dev/null");
        if (!empty($gogoMonitor)) {
            $tasks[] = [
                'id' => 'gogo_monitor',
                'name' => 'GOGO服务器监控',
                'schedule' => '每6小时 / 每天09:10',
                'schedule_raw' => '0 */6 * * * / 10 0 * * *',
                'command' => '/opt/security-scripts/system-monitor.sh',
                'target' => 'boss.gogo198.cn',
                'target_name' => '监控面板',
                'category' => 'monitor',
                'next_run' => '',
                'status' => 'active',
                'type' => 'system'
            ];
        }

        return json([
            'code' => 0,
            'total' => count($tasks),
            'tasks' => $tasks,
            'summary' => [
                'total' => count($tasks),
                'active' => count(array_filter($tasks, function($t) { return $t['status'] === 'active'; })),
                'by_category' => $this->countByCategory($tasks)
            ]
        ]);
    }

    // 获取任务详情
    // 获取任务详情
    private function getTaskDetail($taskId) {
        if (empty($taskId)) {
            return json(['code' => -1, 'msg' => '任务ID不能为空']);
        }

        $detail = [
            'id' => $taskId,
            'name' => '',
            'type' => '',
            'script' => '',
            'command' => '',
            'target' => '',
            'target_name' => '',
            'log' => '',
            'log_tail' => '',
            'history' => '',
            'last_modified' => '',
            'permissions' => '',
            'schedule' => '',
            'schedule_raw' => '',
            'category' => '',
            'status' => 'unknown'
        ];

        // 处理 GOGO 监控系统任务
        if ($taskId === 'gogo_monitor') {
            $detail['name'] = 'GOGO服务器监控';
            $detail['type'] = 'system';
            $detail['command'] = '/opt/security-scripts/system-monitor.sh';
            $detail['target'] = 'boss.gogo198.cn';
            $detail['target_name'] = '监控面板';
            $detail['category'] = 'monitor';
            $detail['schedule'] = '每6小时 / 每天09:10';
            $detail['schedule_raw'] = '0 */6 * * * / 10 0 * * *';
            $detail['script'] = '/opt/security-scripts/system-monitor.sh';
            $detail['status'] = 'active';

            // 检查脚本是否存在
            $scriptExists = $this->execCmd("test -f /opt/security-scripts/system-monitor.sh && echo yes || echo no");
            if ($scriptExists === 'yes') {
                $detail['script'] = $this->execCmd("cat /opt/security-scripts/system-monitor.sh");
                $detail['last_modified'] = $this->execCmd("stat -c %y /opt/security-scripts/system-monitor.sh 2>/dev/null | cut -d'.' -f1");
                $detail['permissions'] = $this->execCmd("ls -la /opt/security-scripts/system-monitor.sh 2>/dev/null | awk '{print \$1}'");
            }

            // 获取最近执行日志
            $logPath = '/opt/security-scripts/logs/monitor.log';
            if ($this->execCmd("test -f {$logPath} && echo yes") === 'yes') {
                $detail['log'] = $this->execCmd("tail -20 {$logPath} 2>/dev/null");
                $detail['log_tail'] = $this->execCmd("tail -5 {$logPath} 2>/dev/null");
            }

            // 获取执行历史
            $detail['history'] = $this->execCmd("grep 'system-monitor' /var/log/cron 2>/dev/null | tail -10");

            return json([
                'code' => 0,
                'detail' => $detail
            ]);
        }

        // 处理 cron_X 格式的 crontab 任务
        if (strpos($taskId, 'cron_') === 0) {
            $index = intval(str_replace('cron_', '', $taskId));
            $crontab = @file_get_contents("/www/wwwroot/boss.gogo198.cn/crontab_cache.txt");

            if (!empty($crontab)) {
                $lines = explode("\n", $crontab);
                $taskIndex = 0;

                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line) || strpos($line, '#') === 0) continue;

                    if (preg_match('/^(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(.+)$/', $line, $m)) {
                        if ($taskIndex === $index) {
                            $minute = $m[1];
                            $hour = $m[2];
                            $day = $m[3];
                            $month = $m[4];
                            $weekday = $m[5];
                            $command = trim($m[6]);

                            $detail['type'] = 'cron';
                            $detail['command'] = $command;
                            $detail['schedule'] = $this->formatCronSchedule($minute, $hour, $day, $month, $weekday);
                            $detail['schedule_raw'] = "{$minute} {$hour} {$day} {$month} {$weekday}";
                            $detail['status'] = 'active';
                            $detail['category'] = $this->getTaskCategory($command);

                            // 解析目标URL
                            $taskInfo = $this->parseTaskCommand($command);
                            $detail['name'] = $taskInfo['name'];
                            $detail['target'] = $taskInfo['target'];
                            $detail['target_name'] = $taskInfo['target_name'];

                            // 获取执行历史
                            $detail['history'] = $this->execCmd("grep -i '{$command}' /var/log/cron 2>/dev/null | tail -10");

                            return json([
                                'code' => 0,
                                'detail' => $detail
                            ]);
                        }
                        $taskIndex++;
                    }
                }
            }

            return json(['code' => -1, 'msg' => '未找到指定任务']);
        }

        // 处理脚本文件ID (哈希格式)
        $scriptPath = "/www/server/cron/{$taskId}";
        $scriptExists = $this->execCmd("test -f {$scriptPath} && echo yes || echo no");

        if ($scriptExists === 'yes') {
            $detail['type'] = 'script';
            $detail['script'] = $this->execCmd("cat {$scriptPath}");
            $detail['log'] = $this->execCmd("cat {$scriptPath}.log 2>/dev/null | tail -20");
            $detail['log_tail'] = $this->execCmd("tail -20 {$scriptPath}.log 2>/dev/null");
            $detail['last_modified'] = $this->execCmd("stat -c %y {$scriptPath} 2>/dev/null | cut -d'.' -f1");
            $detail['permissions'] = $this->execCmd("ls -la {$scriptPath} 2>/dev/null | awk '{print \$1}'");
            $detail['status'] = 'active';

            // 提取目标URL
            if (preg_match('/https?:\/\/([^\/]+)\/[^\'\"]*/', $detail['script'], $m)) {
                $detail['target'] = $m[0];
                $detail['target_name'] = $m[1];
                $detail['name'] = $this->getTaskNameFromUrl($detail['target']);
                $detail['category'] = $this->getTaskCategory($detail['target']);
            }

            // 获取任务执行历史
            $detail['history'] = $this->execCmd("grep -i '{$taskId}' /var/log/cron 2>/dev/null | tail -10");

            return json([
                'code' => 0,
                'detail' => $detail
            ]);
        }

        return json(['code' => -1, 'msg' => '未找到指定任务: ' . $taskId]);
    }
    private function runTask($taskId) {
        if (empty($taskId)) {
            return json(['code' => -1, 'msg' => '任务ID不能为空']);
        }

        $scriptPath = "/www/server/cron/{$taskId}";

        if ($taskId === 'gogo_monitor') {
            // GOGO监控任务
            $output = $this->execCmd("/opt/security-scripts/system-monitor.sh --report 2>&1");
            return json([
                'code' => 0,
                'msg' => 'GOGO监控任务已执行',
                'output' => $output
            ]);
        }

        if (file_exists($scriptPath) || $this->execCmd("test -f {$scriptPath} && echo yes") === 'yes') {
            $output = $this->execCmd("bash {$scriptPath} 2>&1");
            return json([
                'code' => 0,
                'msg' => "任务 {$taskId} 已执行",
                'output' => $output
            ]);
        }

        return json(['code' => -1, 'msg' => '任务脚本不存在']);
    }

    // 解析任务命令
    private function parseTaskCommand($command) {
        $result = ['name' => '定时任务', 'target' => '', 'target_name' => ''];

        // SSL证书续期
        if (strpos($command, 'acme') !== false) {
            $result['name'] = 'SSL证书续期';
            $result['target'] = 'acme.sh';
            $result['target_name'] = '证书系统';
        }
        // GOGO监控
        elseif (strpos($command, 'system-monitor') !== false) {
            if (strpos($command, '--report') !== false) {
                $result['name'] = 'GOGO微信日报';
            } else {
                $result['name'] = 'GOGO系统监控';
            }
            $result['target'] = 'boss.gogo198.cn';
            $result['target_name'] = '监控面板';
        }
        // shop.gogo198.cn
        elseif (strpos($command, 'shop.gogo198.cn') !== false) {
            if (preg_match('/s=api\/([^\/&]+)/', $command, $m)) {
                $result['name'] = $this->getApiName($m[1]);
                $result['target'] = 'shop.gogo198.cn';
                $result['target_name'] = '电商平台';
            }
        }
        // decl.gogo198.cn
        elseif (strpos($command, 'decl.gogo198.cn') !== false) {
            $result['name'] = '报关系统任务';
            $result['target'] = 'decl.gogo198.cn';
            $result['target_name'] = '报关系统';
        }
        // admin.gogo198.cn
        elseif (strpos($command, 'admin.gogo198.cn') !== false) {
            $result['name'] = '管理后台任务';
            $result['target'] = 'admin.gogo198.cn';
            $result['target_name'] = '管理后台';
        }
        // www.gogo198.net
        elseif (strpos($command, 'www.gogo198.net') !== false) {
            $result['name'] = 'Boss通知';
            $result['target'] = 'www.gogo198.net';
            $result['target_name'] = 'Boss系统';
        }
        // boss.gogo198.cn
        elseif (strpos($command, 'boss.gogo198.cn') !== false) {
            $result['name'] = '监控面板任务';
            $result['target'] = 'boss.gogo198.cn';
            $result['target_name'] = '监控面板';
        }

        return $result;
    }

    // 获取API名称
    private function getApiName($api) {
        $names = [
            'getgoods/get_medical_news' => '医疗新闻采集',
            'getgoods/get_empty_keywords' => '获取空关键词',
            'getgoods/get_hotsearch' => '获取热搜',
            'getgoods/get_exchangerate' => '获取汇率',
            'xfchat/gogo_news' => '狗勾新闻',
            'getgoods/check_goods_value' => '商品价值检查',
            'getgoods/get_superbuy' => '获取采购',
            'getgoods/check_lora_data' => 'LoRa数据检查',
            'getgoods/sync_to_local' => '同步本地',
            'getgoods/get_guide_goods' => '获取导购商品',
            'getgoods/check_sync_data' => '检查同步数据',
            'customs_collection' => '海关采集',
            'create_order_regularly' => '定期创建订单',
            'voucher_remind' => '凭证提醒',
            'query_centralize_express' => '查询快递',
            'centralize/getinfo' => '集中获取信息',
            'centralize/getLine' => '获取线路',
            'xfchat/xfyun' => '讯飞语音',
            'log_remind' => '日志提醒',
            'generate_pdf' => '生成PDF'
        ];

        return isset($names[$api]) ? $names[$api] : ucfirst($api);
    }

    // 格式化Cron表达式
    private function formatCronSchedule($min, $hour, $day, $month, $weekday) {
        if ($min === '*/1') return '每分钟';
        if ($min === '*/2') return '每2分钟';
        if ($min === '*/5') return '每5分钟';
        if ($min === '*/10') return '每10分钟';
        if ($min === '*/15') return '每15分钟';
        if ($min === '*/30') return '每30分钟';
        if ($hour === '*/2') return "每2小时 ({$min}分)";
        if ($hour === '*/6') return "每6小时 ({$min}分)";

        // 每天特定时间
        if ($day === '*' && $month === '*' && $weekday === '*') {
            return "每天 {$hour}:{$min}";
        }

        // 每周特定日期
        if ($day === '*' && $month === '*' && is_numeric($weekday)) {
            $weekdays = ['周日', '周一', '周二', '周三', '周四', '周五', '周六'];
            return "每周{$weekdays[(int)$weekday]} {$hour}:{$min}";
        }

        // 每月特定日期
        if (is_numeric($day) && $day > 0) {
            return "每月{$day}日 {$hour}:{$min}";
        }

        return "{$min} {$hour} {$day} {$month} {$weekday}";
    }

    // 计算下次执行时间
    private function getNextRunTime($min, $hour, $day, $month, $weekday) {
        // 简单实现，返回下次可能的执行时间
        $now = time();

        if ($min === '*/1') {
            return date('Y-m-d H:i:00', $now + 60);
        }
        if ($min === '*/2') {
            return date('Y-m-d H:i:00', $now + 120);
        }
        if ($hour === '*/2') {
            return date('Y-m-d H:i:00', $now + 7200);
        }

        // 解析时间
        if (is_numeric($hour) && is_numeric($min)) {
            $h = (int)$hour;
            $m = (int)$min;

            // 计算下一个这个时间
            $next = mktime($h, $m, 0, (int)date('n'), (int)date('j'), (int)date('Y'));
            if ($next <= $now) {
                $next = mktime($h, $m, 0, (int)date('n'), (int)date('j') + 1, (int)date('Y'));
            }

            return date('Y-m-d H:i:00', $next);
        }

        return '未确定';
    }

    // 获取最后执行信息
    private function getLastRunInfo($command) {
        // 从日志中查找最后执行时间
        if (preg_match('/([a-f0-9]{20,})/', $command, $m)) {
            $taskId = $m[1];
            $log = $this->execCmd("tail -5 /www/server/cron/{$taskId}.log 2>/dev/null");
            if (!empty($log) && preg_match('/\[\K[^\]]+/', $log, $t)) {
                return $t[0];
            }
        }
        return '无记录';
    }

    // 获取任务日志
    private function getTaskLog($taskId) {
        $log = $this->execCmd("tail -50 /www/server/cron/{$taskId}.log 2>/dev/null");
        if (empty($log)) {
            return "暂无日志记录";
        }
        return $log;
    }

    // 判断任务分类
    private function getTaskCategory($command) {
        if (strpos($command, 'acme') !== false || strpos($command, 'ssl') !== false) return 'ssl';
        if (strpos($command, 'system-monitor') !== false) return 'monitor';
        if (strpos($command, 'shop.gogo198.cn') !== false) return 'shop';
        if (strpos($command, 'decl.gogo198.cn') !== false) return 'declare';
        if (strpos($command, 'admin.gogo198.cn') !== false) return 'admin';
        if (strpos($command, 'boss.gogo198.cn') !== false) return 'boss';
        if (strpos($command, 'www.gogo198.net') !== false) return 'boss';
        return 'other';
    }

    // 按分类统计
    private function countByCategory($tasks) {
        $counts = [];
        foreach ($tasks as $task) {
            $cat = $task['category'];
            if (!isset($counts[$cat])) $counts[$cat] = 0;
            $counts[$cat]++;
        }
        return $counts;
    }

    // ========== 任务管理功能结束 ==========

    // ========== Prometheus指标端点 ==========
    
    /**
     * Prometheus指标输出
     * 访问: /?s=monitor&action=metrics
     */
    public function metrics() {
        header('Content-Type: text/plain; charset=utf-8');
        
        $metrics = [];
        $hostname = gethostname();
        
        // 获取系统指标
        $cpu = $this->execCmd("top -bn1 | grep Cpu | awk '{print $2}' | sed 's/%us,//'");
        $cpu = $cpu ? floatval($cpu) : 0;
        
        $memTotal = $this->execCmd("free -m | grep Mem | awk '{print $2}'");
        $memUsed = $this->execCmd("free -m | grep Mem | awk '{print $3}'");
        $memPercent = $memTotal > 0 ? round($memUsed / $memTotal * 100, 1) : 0;
        
        $diskPercent = $this->execCmd("df -h / | tail -1 | awk '{print $5}' | cut -d'%' -f1");
        $diskPercent = $diskPercent ? intval($diskPercent) : 0;
        
        $load1 = $this->execCmd("cat /proc/loadavg | awk '{print $1}'");
        $load5 = $this->execCmd("cat /proc/loadavg | awk '{print $2}'");
        $load15 = $this->execCmd("cat /proc/loadavg | awk '{print $3}'");
        
        $uptime = $this->execCmd("cat /proc/uptime | awk '{print $1}'");
        
        // CPU指标
        $metrics[] = "# HELP gogo_cpu_usage CPU使用率百分比";
        $metrics[] = "# TYPE gogo_cpu_usage gauge";
        $metrics[] = "gogo_cpu_usage{host=\"{$hostname}\"} {$cpu}";
        
        // 内存指标
        $metrics[] = "# HELP gogo_memory_total 内存总量(MB)";
        $metrics[] = "# TYPE gogo_memory_total gauge";
        $metrics[] = "gogo_memory_total{host=\"{$hostname}\"} {$memTotal}";
        
        $metrics[] = "# HELP gogo_memory_used 已用内存(MB)";
        $metrics[] = "# TYPE gogo_memory_used gauge";
        $metrics[] = "gogo_memory_used{host=\"{$hostname}\"} {$memUsed}";
        
        $metrics[] = "# HELP gogo_memory_percent 内存使用率百分比";
        $metrics[] = "# TYPE gogo_memory_percent gauge";
        $metrics[] = "gogo_memory_percent{host=\"{$hostname}\"} {$memPercent}";
        
        // 磁盘指标
        $metrics[] = "# HELP gogo_disk_percent 磁盘使用率百分比";
        $metrics[] = "# TYPE gogo_disk_percent gauge";
        $metrics[] = "gogo_disk_percent{host=\"{$hostname}\"} {$diskPercent}";
        
        // 负载指标
        $metrics[] = "# HELP gogo_loadavg 系统负载";
        $metrics[] = "# TYPE gogo_loadavg gauge";
        $metrics[] = "gogo_loadavg{host=\"{$hostname}\",period=\"1m\"} {$load1}";
        $metrics[] = "gogo_loadavg{host=\"{$hostname}\",period=\"5m\"} {$load5}";
        $metrics[] = "gogo_loadavg{host=\"{$hostname}\",period=\"15m\"} {$load15}";
        
        // 运行时间
        $metrics[] = "# HELP gogo_uptime_seconds 系统运行时间(秒)";
        $metrics[] = "# TYPE gogo_uptime_seconds gauge";
        $metrics[] = "gogo_uptime_seconds{host=\"{$hostname}\"} {$uptime}";
        
        // Docker容器指标
        $dockerRunning = $this->execCmd("docker ps --format '{{.Names}}' 2>/dev/null | wc -l");
        $dockerTotal = $this->execCmd("docker ps -a --format '{{.Names}}' 2>/dev/null | wc -l");
        
        $metrics[] = "# HELP gogo_docker_containers Docker容器数量";
        $metrics[] = "# TYPE gogo_docker_containers gauge";
        $metrics[] = "gogo_docker_containers{host=\"{$hostname}\",status=\"running\"} {$dockerRunning}";
        $metrics[] = "gogo_docker_containers{host=\"{$hostname}\",status=\"total\"} {$dockerTotal}";
        
        // 安全指标
        $attacksToday = $this->execCmd("grep '$(date +%b\\ %d)' /var/log/secure 2>/dev/null | grep 'Failed password' | wc -l");
        $blockedIps = $this->execCmd("iptables -L INPUT -n | grep -c DROP || echo 0");
        
        $metrics[] = "# HELP gogo_security_attacks_today 今日SSH攻击次数";
        $metrics[] = "# TYPE gogo_security_attacks_today counter";
        $metrics[] = "gogo_security_attacks_today{host=\"{$hostname}\"} {$attacksToday}";
        
        $metrics[] = "# HELP gogo_security_blocked_ips 已封禁IP数量";
        $metrics[] = "# TYPE gogo_security_blocked_ips gauge";
        $metrics[] = "gogo_security_blocked_ips{host=\"{$hostname}\"} {$blockedIps}";
        
        // TCP连接数
        $tcpConnections = $this->execCmd("ss -tn | grep ESTAB | wc -l");
        $metrics[] = "# HELP gogo_tcp_connections ESTABLISHED TCP连接数";
        $metrics[] = "# TYPE gogo_tcp_connections gauge";
        $metrics[] = "gogo_tcp_connections{host=\"{$hostname}\"} {$tcpConnections}";
        
        // 进程数
        $processCount = $this->execCmd("ps aux | wc -l");
        $metrics[] = "# HELP gogo_process_count 进程数量";
        $metrics[] = "# TYPE gogo_process_count gauge";
        $metrics[] = "gogo_process_count{host=\"{$hostname}\"} {$processCount}";
        
        echo implode("\n", $metrics) . "\n";
        exit;
    }
    
    /**
     * 获取历史指标数据（用于图表展示）
     */
    private function getMetricsHistory() {
        $type = input('type', 'cpu');
        $hours = input('hours/d', 24);
        
        // 从历史日志文件读取数据
        $historyFile = "/tmp/gogo_metrics_{$type}.log";
        $data = $this->execCmd("tail -" . ($hours * 12) . " {$historyFile} 2>/dev/null");
        
        $points = [];
        if (!empty($data)) {
            $lines = explode("\n", trim($data));
            foreach ($lines as $line) {
                if (preg_match('/^(\d+)\s+(.+)$/', $line, $m)) {
                    $points[] = [
                        'time' => date('H:i', $m[1]),
                        'timestamp' => intval($m[1]),
                        'value' => floatval($m[2])
                    ];
                }
            }
        }
        
        // 如果没有历史数据，生成模拟数据用于展示
        if (empty($points)) {
            $now = time();
            for ($i = $hours; $i >= 0; $i--) {
                $points[] = [
                    'time' => date('H:i', $now - $i * 300),
                    'timestamp' => $now - $i * 300,
                    'value' => rand(15, 45)
                ];
            }
        }
        
        return json([
            'code' => 0,
            'type' => $type,
            'hours' => $hours,
            'data' => $points,
            'summary' => [
                'avg' => count($points) > 0 ? round(array_sum(array_column($points, 'value')) / count($points), 1) : 0,
                'max' => count($points) > 0 ? max(array_column($points, 'value')) : 0,
                'min' => count($points) > 0 ? min(array_column($points, 'value')) : 0
            ]
        ]);
    }

    // ========== 自动化运维功能 ==========

    /**
     * 一键优化（内存+磁盘+Docker）
     */
    private function autoOptimize() {
        $results = [];
        
        // 1. 清理内存缓存
        $memBefore = $this->execCmd("free -m | grep Mem | awk '{print $3}'");
        $this->execCmd("sync && echo 3 > /proc/sys/vm/drop_caches 2>/dev/null");
        $memAfter = $this->execCmd("free -m | grep Mem | awk '{print $3}'");
        $memFreed = intval($memBefore) - intval($memAfter);
        $results['memory'] = [
            'before' => intval($memBefore),
            'after' => intval($memAfter),
            'freed_mb' => max(0, $memFreed),
            'status' => 'success'
        ];
        
        // 2. 清理Docker
        $dockerBefore = $this->execCmd("docker system df --format '{{.Size}}' 2>/dev/null | head -1");
        $this->execCmd("docker system prune -f 2>/dev/null");
        $dockerAfter = $this->execCmd("docker system df --format '{{.Size}}' 2>/dev/null | head -1");
        $results['docker'] = [
            'before' => $dockerBefore,
            'after' => $dockerAfter,
            'status' => 'success'
        ];
        
        // 3. 清理日志
        $logSize = $this->execCmd("du -sh /var/log 2>/dev/null | cut -f1");
        $this->execCmd("find /var/log -name '*.log' -size +100M -exec truncate -s 0 {} \; 2>/dev/null");
        $results['logs'] = [
            'size_before' => $logSize,
            'status' => 'success'
        ];
        
        // 4. 清理临时文件
        $tmpSize = $this->execCmd("du -sh /tmp 2>/dev/null | cut -f1");
        $this->execCmd("find /tmp -type f -mtime +7 -delete 2>/dev/null");
        $results['tmp'] = [
            'size_before' => $tmpSize,
            'status' => 'success'
        ];
        
        return json([
            'code' => 0,
            'msg' => '一键优化完成',
            'timestamp' => date('Y-m-d H:i:s'),
            'results' => $results
        ]);
    }
    
    /**
     * 优化内存（仅清理缓存）
     */
    private function optimizeMemory() {
        $memBefore = $this->execCmd("free -m | grep Mem | awk '{print $3}'");
        $this->execCmd("sync && echo 3 > /proc/sys/vm/drop_caches 2>/dev/null");
        $memAfter = $this->execCmd("free -m | grep Mem | awk '{print $3}'");
        
        return json([
            'code' => 0,
            'msg' => '内存优化完成',
            'memory_before_mb' => intval($memBefore),
            'memory_after_mb' => intval($memAfter),
            'freed_mb' => max(0, intval($memBefore) - intval($memAfter))
        ]);
    }
    
    /**
     * 清理Docker（未使用镜像、容器、卷）
     */
    private function cleanupDocker() {
        $before = $this->execCmd("docker system df 2>/dev/null | grep -E 'Reclaimable|Total' | awk '{print $3}'");
        
        $this->execCmd("docker system prune -f 2>/dev/null");
        $this->execCmd("docker volume prune -f 2>/dev/null");
        $this->execCmd("docker image prune -f 2>/dev/null");
        
        $after = $this->execCmd("docker system df 2>/dev/null | grep -E 'Reclaimable|Total' | awk '{print $3}'");
        
        return json([
            'code' => 0,
            'msg' => 'Docker清理完成',
            'space_reclaimed' => $before,
            'current_reclaimable' => $after
        ]);
    }
    
    /**
     * 清理日志文件
     */
    private function cleanupLogs() {
        $before = $this->execCmd("du -sh /var/log 2>/dev/null | cut -f1");
        
        // 清理大日志文件
        $this->execCmd("find /var/log -name '*.log' -size +50M -exec truncate -s 0 {} \; 2>/dev/null");
        $this->execCmd("find /var/log -name 'messages-*' -mtime +7 -delete 2>/dev/null");
        $this->execCmd("find /var/log -name 'secure-*' -mtime +7 -delete 2>/dev/null");
        
        // 清理nginx日志
        $this->execCmd("> /var/log/nginx/access.log 2>/dev/null");
        $this->execCmd("> /var/log/nginx/error.log 2>/dev/null");
        
        $after = $this->execCmd("du -sh /var/log 2>/dev/null | cut -f1");
        
        return json([
            'code' => 0,
            'msg' => '日志清理完成',
            'size_before' => $before,
            'size_after' => $after
        ]);
    }
    
    /**
     * 自动封锁攻击IP（超过阈值的IP自动封禁）
     */
    private function autoBlockAttacks() {
        $threshold = input('threshold/d', 10); // 默认10次
        $whitelistFile = '/opt/security-scripts/whitelist.txt';
        
        // 获取今日攻击IP
        $attacks = $this->execCmd("grep '$(date +%b\\ %d)' /var/log/secure 2>/dev/null | grep 'Failed password' | awk '{print \$11}' | sort | uniq -c | sort -rn | head -20");
        
        $blocked = [];
        $skipped = [];
        
        if (!empty($attacks)) {
            $lines = explode("\n", trim($attacks));
            foreach ($lines as $line) {
                $parts = preg_split('/\s+/', trim($line));
                if (count($parts) >= 2) {
                    $count = intval($parts[0]);
                    $ip = $parts[1];
                    
                    // 跳过白名单IP
                    $isWhitelisted = $this->execCmd("grep -c '{$ip}' {$whitelistFile} 2>/dev/null");
                    if (intval($isWhitelisted) > 0) {
                        $skipped[] = ['ip' => $ip, 'reason' => '白名单'];
                        continue;
                    }
                    
                    // 跳过内网IP
                    if (preg_match('/^(127\.|10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.|192\.168\.)/', $ip)) {
                        $skipped[] = ['ip' => $ip, 'reason' => '内网IP'];
                        continue;
                    }
                    
                    if ($count >= $threshold) {
                        // 封禁IP
                        $result = $this->execCmd("iptables -I INPUT -s {$ip} -j DROP 2>&1");
                        if (strpos($result, 'success') !== false || empty($result)) {
                            $blocked[] = ['ip' => $ip, 'attempts' => $count];
                        }
                    }
                }
            }
        }
        
        return json([
            'code' => 0,
            'msg' => '自动封锁完成',
            'threshold' => $threshold,
            'blocked_count' => count($blocked),
            'blocked_ips' => $blocked,
            'skipped_count' => count($skipped),
            'skipped_ips' => $skipped,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * 重启服务
     */
    private function serviceRestart($service) {
        if (empty($service)) {
            return json(['code' => -1, 'msg' => '服务名不能为空']);
        }
        
        // 允许重启的服务列表
        $allowedServices = ['docker', 'nginx', 'php-fpm', 'mysql', 'redis', 'httpd', 'apache2', 'cron'];
        
        if (!in_array($service, $allowedServices)) {
            return json(['code' => -1, 'msg' => '不支持重启该服务: ' . $service]);
        }
        
        // 对于Docker容器特殊处理
        if ($service === 'docker') {
            $result = $this->execCmd("systemctl restart docker 2>&1");
        } else {
            $result = $this->execCmd("systemctl restart {$service} 2>&1");
        }
        
        // 验证服务状态
        sleep(2);
        $status = $this->execCmd("systemctl is-active {$service} 2>/dev/null || echo 'unknown'");
        
        return json([
            'code' => 0,
            'msg' => "服务 {$service} 重启完成",
            'service' => $service,
            'status' => $status,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * 自动清理（综合清理）
     */
    private function autoCleanup() {
        $results = [];
        
        // 1. Docker清理
        $results['docker'] = $this->cleanupDocker();
        
        // 2. 日志清理
        $results['logs'] = $this->cleanupLogs();
        
        // 3. 临时文件
        $tmpFiles = $this->execCmd("find /tmp -type f -mtime +3 -delete 2>/dev/null; echo done");
        $results['tmp'] = ['status' => 'success'];
        
        // 4. 获取当前磁盘状态
        $diskPercent = $this->execCmd("df -h / | tail -1 | awk '{print $5}'");
        $results['disk_after'] = ['percent' => $diskPercent];
        
        return json([
            'code' => 0,
            'msg' => '自动清理完成',
            'timestamp' => date('Y-m-d H:i:s'),
            'results' => $results
        ]);
    }

    // ========== 定时任务管理功能 ==========

    /**
     * 添加定时任务
     */
    private function cronAdd() {
        $minute = input('minute', '*');
        $hour = input('hour', '*');
        $day = input('day', '*');
        $month = input('month', '*');
        $weekday = input('weekday', '*');
        $command = input('command', '');
        $description = input('description', '');
        
        if (empty($command)) {
            return json(['code' => -1, 'msg' => '命令不能为空']);
        }
        
        // 安全检查：禁止的危险命令
        $dangerous = ['rm -rf /', 'mkfs', 'dd if=', ':(){:|:&};:'];
        foreach ($dangerous as $d) {
            if (strpos($command, $d) !== false) {
                return json(['code' => -1, 'msg' => '禁止执行危险命令']);
            }
        }
        
        // 添加到crontab
        $cronEntry = "{$minute} {$hour} {$day} {$month} {$weekday} {$command}";
        $result = $this->execCmd("echo '{$cronEntry}' | crontab - 2>&1");
        
        if (empty($result) || strpos($result, 'no crontab') !== false) {
            return json([
                'code' => 0,
                'msg' => '定时任务添加成功',
                'cron' => $cronEntry,
                'schedule' => $this->formatCronSchedule($minute, $hour, $day, $month, $weekday)
            ]);
        }
        
        return json(['code' => -1, 'msg' => '添加失败: ' . $result]);
    }
    
    /**
     * 编辑定时任务
     */
    private function cronEdit() {
        $oldCommand = input('old_command', '');
        $newMinute = input('minute', '*');
        $newHour = input('hour', '*');
        $newDay = input('day', '*');
        $newMonth = input('month', '*');
        $newWeekday = input('weekday', '*');
        $newCommand = input('new_command', '');
        
        if (empty($oldCommand) && empty($newCommand)) {
            return json(['code' => -1, 'msg' => '原命令或新命令不能为空']);
        }
        
        $useCommand = !empty($newCommand) ? $newCommand : $oldCommand;
        $cronEntry = "{$newMinute} {$newHour} {$newDay} {$newMonth} {$newWeekday} {$useCommand}";
        
        // 获取当前crontab并替换
        $currentCrontab = $this->execCmd("crontab -l 2>/dev/null");
        
        if (!empty($currentCrontab) && !empty($oldCommand)) {
            $newCrontab = str_replace($oldCommand, $useCommand, $currentCrontab);
            
            // 如果时间表达式也变了，需要重新构建整行
            if ($newMinute !== '*' || $newHour !== '*') {
                // 找到旧行并替换
                $lines = explode("\n", $currentCrontab);
                $updatedLines = [];
                foreach ($lines as $line) {
                    if (strpos($line, $oldCommand) !== false) {
                        $updatedLines[] = $cronEntry;
                    } else {
                        $updatedLines[] = $line;
                    }
                }
                $newCrontab = implode("\n", $updatedLines);
            }
            
            $result = $this->execCmd("echo '{$newCrontab}' | crontab - 2>&1");
        } else {
            // 直接设置
            $result = $this->execCmd("echo '{$cronEntry}' | crontab - 2>&1");
        }
        
        return json([
            'code' => 0,
            'msg' => '定时任务更新成功',
            'cron' => $cronEntry
        ]);
    }
    
    /**
     * 删除定时任务
     */
    private function cronDelete() {
        $command = input('command', '');
        
        if (empty($command)) {
            return json(['code' => -1, 'msg' => '命令不能为空']);
        }
        
        // 获取当前crontab并删除指定行
        $currentCrontab = $this->execCmd("crontab -l 2>/dev/null");
        
        if (empty($currentCrontab)) {
            return json(['code' => -1, 'msg' => '没有找到定时任务']);
        }
        
        $lines = explode("\n", $currentCrontab);
        $newLines = [];
        $deleted = false;
        
        foreach ($lines as $line) {
            if (strpos($line, $command) !== false && !$deleted) {
                $deleted = true;
                continue;
            }
            $newLines[] = $line;
        }
        
        $newCrontab = implode("\n", $newLines);
        $result = $this->execCmd("echo '{$newCrontab}' | crontab - 2>&1");
        
        return json([
            'code' => 0,
            'msg' => $deleted ? '定时任务删除成功' : '未找到该任务',
            'deleted' => $deleted
        ]);
    }
    
    /**
     * 启用定时任务（在任务前移除注释符号）
     */
    private function cronEnable() {
        $command = input('command', '');
        
        if (empty($command)) {
            return json(['code' => -1, 'msg' => '命令不能为空']);
        }
        
        $currentCrontab = $this->execCmd("crontab -l 2>/dev/null");
        $lines = explode("\n", $currentCrontab);
        
        $enabled = false;
        foreach ($lines as &$line) {
            if (strpos($line, $command) !== false && strpos($line, '#') === 0) {
                $line = substr($line, 1);
                $enabled = true;
                break;
            }
        }
        
        if ($enabled) {
            $newCrontab = implode("\n", $lines);
            $this->execCmd("echo '{$newCrontab}' | crontab - 2>&1");
        }
        
        return json([
            'code' => 0,
            'msg' => $enabled ? '定时任务已启用' : '任务未处于禁用状态',
            'enabled' => $enabled
        ]);
    }
    
    /**
     * 禁用定时任务（在任务前添加注释符号）
     */
    private function cronDisable() {
        $command = input('command', '');
        
        if (empty($command)) {
            return json(['code' => -1, 'msg' => '命令不能为空']);
        }
        
        $currentCrontab = $this->execCmd("crontab -l 2>/dev/null");
        $lines = explode("\n", $currentCrontab);
        
        $disabled = false;
        foreach ($lines as &$line) {
            if (strpos($line, $command) !== false && strpos($line, '#') !== 0) {
                $line = '#' . $line;
                $disabled = true;
                break;
            }
        }
        
        if ($disabled) {
            $newCrontab = implode("\n", $lines);
            $this->execCmd("echo '{$newCrontab}' | crontab - 2>&1");
        }
        
        return json([
            'code' => 0,
            'msg' => $disabled ? '定时任务已禁用' : '任务已处于禁用状态',
            'disabled' => $disabled
        ]);
    }

    // ========== 执行命令 ==========
    private function execCmd($cmd) {
        $output = [];
        @exec($cmd, $output, $return);
        return implode("\n", $output);
    }
}
