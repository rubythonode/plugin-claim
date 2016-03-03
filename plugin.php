<?php
/**
 * Claim plugin
 *
 * PHP version 5
 *
 * @category    Claim
 * @package     Claim
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
namespace Xpressengine\Plugins\Claim;

use Xpressengine\Plugin\AbstractPlugin;
use Route;
use Schema;
use Illuminate\Database\Schema\Blueprint;
use XeDB;

/**
 * Claim plugin
 *
 * @category    Claim
 * @package     Claim
 * @author      XE Team (akasima) <osh@xpressengine.com>
 * @copyright   2014 Copyright (C) NAVER <http://www.navercorp.com>
 * @license     http://www.gnu.org/licenses/lgpl-3.0-standalone.html LGPL
 * @link        http://www.xpressengine.com
 */
class Plugin extends AbstractPlugin
{
    /**
     * @return boolean
     */
    public function unInstall()
    {
        // TODO: Implement unInstall() method.
    }

    /**
     * @param null $installedVersion install version
     * @return bool
     */
    public function checkInstalled($installedVersion = null)
    {
        if ($installedVersion === null) {
            return false;
        }
    }

    /**
     * @return boolean
     */
    public function checkUpdated($installedVersion = NULL)
    {
        // TODO: Implement checkUpdate() method.
    }

    /**
     * boot
     *
     * @return void
     */
    public function boot()
    {
        $this->registerToggleMenu();
        $this->registerManageRoute();
        $this->registerFixedRoute();
        $this->registerSettingsMenu();

        $app = app();
        $app['xe.claim.handler'] = $app->share(
            function ($app) {
                $repository = new ClaimRepository(XeDB::connection());
                $handler = new Handler($repository, app('xe.config'), app('xe.members'));
                return $handler;
            }
        );
    }

    /**
     * activate
     *
     * @param null $installedVersion installed version
     * @return void
     */
    public function activate($installedVersion = null)
    {
    }

    public function install()
    {
        if (Schema::hasTable('claim_log') === false) {
            Schema::create('claim_log', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('claimType', 36);
                $table->string('shortCut', 255);
                $table->string('targetId', 36);
                $table->string('userId', 36);
                $table->string('ipaddress', 16);
                $table->timestamp('createdAt');

                $table->index(['targetId', 'userId']);
                $table->index(['targetId', 'ClaimType']);
            });
        }
    }

    /**
     * register toggle menu
     *
     * @return void
     */
    protected function registerToggleMenu()
    {
        app('xe.pluginRegister')->add(ToggleMenus\BoardClaimItem::class);
        app('xe.pluginRegister')->add(ToggleMenus\CommentClaimItem::class);
    }

    /**
     * Register Plugin Settings Route
     *
     * @return void
     */
    protected function registerManageRoute()
    {
        Route::settings(self::getId(), function () {
            Route::get('/', ['as' => 'manage.claim.claim.index', 'uses' => 'ManagerController@index']);
            Route::get('delete', ['as' => 'manage.claim.claim.delete', 'uses' => 'ManagerController@delete']);
            Route::get('config', ['as' => 'manage.claim.claim.config', 'uses' => 'ManagerController@config']);
            Route::get(
                'config/edit',
                ['as' => 'manage.claim.claim.config.edit', 'uses' => 'ManagerController@configEdit']
            );
            Route::post(
                'config/update',
                ['as' => 'manage.claim.claim.config.update', 'uses' => 'ManagerController@configUpdate']
            );
        }, ['namespace' => 'Xpressengine\Plugins\Claim']);
    }

    /**
     * register fixed route
     *
     * @return void
     */
    protected function registerFixedRoute()
    {
        Route::fixed('claim', function () {
            Route::get('', ['as' => 'fixed.claim.index', 'uses' => 'UserController@index']);
            Route::post('store', ['as' => 'fixed.claim.store', 'uses' => 'UserController@store']);
            Route::post('destroy', ['as' => 'fixed.claim.destroy', 'uses' => 'UserController@destroy']);
        }, ['namespace' => 'Xpressengine\Plugins\Claim']);
    }

    /**
     * register interception
     *
     * @return void
     */
    public static function registerSettingsMenu()
    {
        // settings menu 등록
        $menus = [
            'contents.claim' => [
                'title' => '신고 관리',
                'display' => true,
                'description' => 'blur blur~',
                'link' => route('manage.claim.claim.index'),
                'ordering' => 5000
            ],
        ];
        foreach ($menus as $id => $menu) {
            app('xe.register')->push('settings/menu', $id, $menu);
        }
    }
}
