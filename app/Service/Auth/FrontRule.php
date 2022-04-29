<?php


namespace App\Service\Auth;


use App\Models\Member\Rule;
use App\Models\Member\RuleGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FrontRule
{

    private $siteNameSuffix = '';


    private $successNum = 0;

    public function handle($routes, $group = '', $groupUuid = null)
    {
        foreach ($routes as $route) {
            if (isset($route['children']) && is_array($route['children']) && sizeof($route['children'])) {

                if (!empty($route['group'])) {
                    $groupUuid = $this->getRuleGroup($route['group']);
                }
                $this->handle($route['children'], $route['group'] ?? '', $groupUuid,);
            } else {

                if (!empty($route['name'])) {
                    $flag = $this->addRule($route, $group, $groupUuid);
                    if ($flag) {
                        $this->successNum++;
                    }
                }
            }
        }
    }

    private function handleChildren($routes, $group = '', $groupUuid = null)
    {
        foreach ($routes as $route) {
            if (isset($route['children']) && is_array($route['children']) && sizeof($route['children'])) {
                if (!empty($route['group'])) {
                    $groupUuid = $this->getRuleGroup($route['group']);
                }
                $this->handleChildren($route['children'], $route['group'] ?? '', $groupUuid,);
            } else {
                if (!empty($route['name'])) {
                    $flag = $this->addRule($route);
                    if ($flag) {
                        $this->successNum++;
                    }
                }
            }
        }
    }

    private function addRule($route, $group = '', $groupUuid = null)
    {
        if (empty($groupUuid)) {
            $ruleGroup = $this->getDefaultGroup();
            $groupUuid = $ruleGroup['uuid'];
            $group = $ruleGroup['name'];
        }
        /** @var  $ruleGroup Model */
        $ruleName = $this->resolveName($route);
        $ruleInfo = $group . $ruleName;


        /** @var  $ruleModel Builder */
        $ruleModel = new Rule();
        $rule = $ruleModel->where(['rule_code' => $route['name']])->first();
        if ($rule) {
            $rule->rule_name = $ruleName;
            $rule->rule_info = $ruleInfo;
            $rule->rule_group_uuid = $groupUuid;
            $rule->type = 2;
            return $rule->save();
        }
        $rule = $ruleModel->create([
            'rule_uuid' => getUuid(),
            'rule_name' => $ruleName,
            'rule_info' => $ruleInfo,
            'rule_code' => $route['name'],
            'rule_group_uuid' => $groupUuid,
            'type' => 2,
            'create_user' => onlineMember()->getUuid()
        ]);
        if ($rule) {
            return true;
        }
        return false;
    }

    /**
     * 获取分组uuid
     * @param $groupName
     * @date : 2021/6/15 19:45
     * @return string
     * @author : 孤鸿渺影
     */
    private function getRuleGroup($groupName): string
    {
        /** @var $ruleGroupModel Builder */
        $ruleGroupModel = new RuleGroup();
        $ruleGroup = $ruleGroupModel->where(['rule_group_name' => $groupName])->first();
        if ($ruleGroup) {
            return $ruleGroup->rule_group_uuid;
        }
        $ruleGroup = $ruleGroupModel->create([
            'rule_group_uuid' => getUuid(),
            'rule_group_name' => $groupName,
            'rule_group_info' => $groupName,
            'create_user' => onlineMember()->getUuid()
        ]);
        if ($ruleGroup) {
            return $ruleGroup->rule_group_uuid;
        }
        return '';
    }

    /**
     * 获取默认分组
     * @date : 2021/6/15 19:45
     * @return array
     * @author : 孤鸿渺影
     */
    private function getDefaultGroup(): array
    {
        return ['uuid' => $this->getRuleGroup('前端'), 'name' => '前端'];
    }

    private function resolveName($route)
    {
        if (isset($route['meta']) && !empty($route['meta']['title'])) {
            $title = $route['meta']['title'];
            !empty($this->siteNameSuffix) && $title = str_replace($this->siteNameSuffix, '', $title);
            return $title;
        }
        return $route['name'];
    }

    /**
     * @return int
     */
    public function getSuccessNum(): int
    {
        return $this->successNum;
    }

    /**
     * @return string
     */
    public function getSiteNameSuffix(): string
    {
        return $this->siteNameSuffix;
    }

    /**
     * @param string $siteNameSuffix
     */
    public function setSiteNameSuffix(string $siteNameSuffix): void
    {
        $this->siteNameSuffix = $siteNameSuffix;
    }


}
