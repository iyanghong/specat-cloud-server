<?php

namespace App\Console\Commands\Auth;

use App\Core\Utils\DocParser;
use App\Models\Member\Rule;
use App\Models\Member\RuleGroup;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class RefreshAuth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:refresh {update=true}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '刷新权限';

    /**
     * @var array 错误路由
     */
    private array $failRoute = [];

    private bool $update = true;


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *
     * @date : 2021/5/8 23:39
     * @return int
     * @throws \ReflectionException
     * @author : 孤鸿渺影
     */
    public function handle()
    {
        $this->update = $this->argument('update');
        $routes = \Illuminate\Support\Facades\Route::getRoutes()->get();
        $total = sizeof($routes);
        $successCount = 0;
        $failCount = 0;
        foreach ($routes as $route) {
            $name = $route->getName();
            if (stripos($name, 'horizon.') !== false) continue;
            if ($name) {
                $actionName = $route->getActionName();
                try {
                    if (stripos($actionName, '@') !== false) {
                        $action = explode('@', $actionName);
                        $data = $this->resolverAction(...$action);
                        if ($data) {
                            $flag = $this->store($route, $data);
                            if ($flag) {
                                $this->info(sprintf('路由[%s]解析成功', $route->uri()));
                                $successCount++;
                            }else{
                                $failCount++;
                                $this->error(sprintf('路由[%s]解析失败：%s', $route->uri(),'存储失败'));
                            }
                            continue;
                        }
                    }
                } catch (\Exception $exception) {
                    $failCount++;
                    $this->error(sprintf('路由[%s]解析失败：%s', $route->uri(),$exception->getMessage()));
                }
                $failCount++;
                $this->error(sprintf('路由[%s]解析失败', $route->uri()));
            }
        }
        $waring = $total - $successCount - $failCount;
        echo sprintf("成功 [\033[36m%s\033[0m] 失败 [\e[33m%s\e[0m] 失效 [\e[31m%s\e[0m]\n",$successCount,$failCount,$waring);
        return 0;
    }


    private function store(\Illuminate\Routing\Route $route, $data)
    {
        $code = $route->getName();
        /* @var $ruleGroupModel Builder */
        $ruleGroupModel = new RuleGroup();
        $ruleGroup = $ruleGroupModel->where([
            'rule_group_name' => $data['group']
        ])->first();
        if (!$ruleGroup) {
            $ruleGroup = $ruleGroupModel->create([
                'rule_group_uuid' => getUuid(),
                'rule_group_name' => $data['group'],
                'rule_group_info' => $data['group'],
                'create_user' => onlineMember()->getId()
            ]);
            if (!$ruleGroup) {
                $this->error(sprintf('创建规则组[%s]失败', $data['group']));
                return false;
            }
        }
        /* @var $ruleModel Builder */
        $ruleModel = new Rule();
        $rule = $ruleModel->where([
            'rule_code' => $code
        ])->first();

        if ($rule && $this->update) {
            $rule->rule_name = $data['name'];
            $rule->rule_info = $data['group'] . $data['name'];
            $rule->rule_group_uuid = $ruleGroup->rule_group_uuid;
            $rule->save();
            return true;
        } elseif (!$rule) {
            $rule = $ruleModel->create([
                'rule_uuid' => getUuid(),
                'rule_name' => $data['name'],
                'rule_info' => $data['group'] . $data['name'],
                'rule_code' => $code,
                'rule_group_uuid' => $ruleGroup->rule_group_uuid,
                'create_user' => onlineMember()->getId()
            ]);
            if ($rule) {
                return true;
            }
        }
        return false;
    }

    /**
     * 解析action
     * @param $class
     * @param $method
     * @date : 2021/5/8 23:30
     * @return bool|array
     * @throws \ReflectionException
     * @author : 孤鸿渺影
     */
    private function resolverAction($class, $method)
    {
        if (!class_exists($class)) {
            $this->error(sprintf('类[%s]不存在', $class));
            return false;
        }
        $reflection = new \ReflectionClass($class);
        if (!$reflection->hasMethod($method)) {
            $this->error(sprintf('方法[%s]不存在', $method));
            return false;
        }
        $groupNameTmp = explode('\\', $reflection->getName());
        $groupName = str_replace('Controller', '', last($groupNameTmp));
        $name = $method;

        $classDocumentParam = $this->resolverDocument($reflection->getDocComment());
        if (isset($classDocumentParam['description'])) {
            $groupName = $classDocumentParam['description'];
        }

        $method = $reflection->getMethod($method);
        //解析方法注释
        $methodDocumentParam = $this->resolverDocument($method->getDocComment());
        if (isset($methodDocumentParam['description'])) {
            $name = $methodDocumentParam['description'];
        }

        return [
            'group' => $groupName,
            'name' => $name
        ];

    }

    /**
     * 解析注释
     * @param $document
     * @date : 2021/5/8 23:37
     * @return array
     * @author : 孤鸿渺影
     */
    private function resolverDocument($document)
    {
        $docParser = new DocParser();
        return $docParser->parse($document);
    }
}
