<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class AdminAccess
{
    public function handle($request, Closure $next)
    {
        $routeName = Route::currentRouteName();

        if (!empty($routeName)) {
            $bypassSpaShellRoutes = [
                'result.downloadMarksheet',
                'result.marksheetAllData',
                'result.marksheetAllDownload',
                'result.downloadBulkMarksheet',
                'result.bulkMarksheetStatus',
                'result.downloadBulkMarksheetFile',
                'result.exportTabulationSheet',
                'role.getPermissions',
            ];

            if (in_array($routeName, $bypassSpaShellRoutes, true)) {
                return $next($request);
            }

            $permitedMenuArr = App::make('premitedMenuArr');

            if (Str::startsWith($routeName, 'content.')) {
                $canCreate = in_array('content.create', $permitedMenuArr);
                $canShow = in_array('content.show', $permitedMenuArr) || $canCreate;

                if (in_array($routeName, ['content.create', 'content.store', 'content.storeFile', 'content.destroy'], true)) {
                    if (!$canCreate) {
                        return abort(403);
                    }
                } else {
                    if (!$canShow) {
                        return abort(403);
                    }
                }

                if ($request->ajax() || $request->wantsJson() || $request->format() != 'html') {
                    return $next($request);
                }

                return response(view('layouts.backend_app'));
            }

            $websiteModules = ['slider', 'videoSlider', 'bus', 'calender', 'class', 'examR', 'popup', 'notice', 'student'];
            foreach ($websiteModules as $mod) {
                if (!Str::startsWith($routeName, $mod . '.')) {
                    continue;
                }

                $canIndex = in_array("{$mod}.index", $permitedMenuArr)
                    || in_array("{$mod}.show", $permitedMenuArr)
                    || in_array("{$mod}.create", $permitedMenuArr)
                    || in_array("{$mod}.edit", $permitedMenuArr);
                $canCreate = in_array("{$mod}.create", $permitedMenuArr);
                $canEdit = in_array("{$mod}.edit", $permitedMenuArr);
                $canDestroy = in_array("{$mod}.destroy", $permitedMenuArr);

                if (in_array($routeName, ["{$mod}.create", "{$mod}.store"], true)) {
                    if (!$canCreate) {
                        return abort(403);
                    }
                } elseif (in_array($routeName, ["{$mod}.edit", "{$mod}.update"], true)) {
                    if (!$canEdit) {
                        return abort(403);
                    }
                } elseif ($routeName === "{$mod}.destroy") {
                    if (!$canDestroy) {
                        return abort(403);
                    }
                } else {
                    if (!$canIndex) {
                        return abort(403);
                    }
                }

                if ($request->ajax() || $request->wantsJson() || $request->format() != 'html') {
                    return $next($request);
                }

                return response(view('layouts.backend_app'));
            }

            if (!in_array($routeName, $permitedMenuArr)) {
                return abort(403);
            }

            if ($request->ajax() || $request->wantsJson() || $request->format() != 'html') {
                return $next($request);
            }

            return response(view('layouts.backend_app'));
        }

        return $next($request);
    }
}
