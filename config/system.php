<?php
/**
 * 系统运行配置
 */
return [
    'permissions' => true,
    'permissionsIgnoreLogin' => [
        'login',
        'logout',
        'account.search',
        'account.login.check',
        'map.*',
        'blog.article.list*',
        'account.check.*',
        'account.sendRegisterEmailVerifyCode',
        'blog.article.detail',
        'blog.article.comment.tree',
        'blog.article.comment.reply',
        'blog.article.comment.list',
        'blog.friendLink.all',
        'blog.leave.*',
        'log.logUserStatus.get',
        'statistic.blog.total',
        'tool.data.generate.personal',
        'blog.label.index',
        'blog.category.index',
        'statistic.visited.store',
        'blog.articleLike.store'
    ],
    'permissionsIgnoreAuth' => [
        'account.*'
    ],
    'blogArticleDetailUrl' => 'https://www.yhong.info/article/${id}.html'
];