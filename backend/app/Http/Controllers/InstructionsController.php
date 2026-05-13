<?php

namespace App\Http\Controllers;

use App\Models\ClientOperatingSystem;
use Illuminate\Http\Response;

class InstructionsController extends Controller
{
    public function setup(string $os): Response
    {
        return $this->getInstructions('setup', $os);
    }

    public function connection(string $os): Response
    {
        return $this->getInstructions('connection', $os);
    }

    private function getInstructions(string $type, string $os): Response
    {
        $operatingSystem = ClientOperatingSystem::where('slug', $os)->first();

        if (!$operatingSystem) {
            abort(404, 'Operating system not found');
        }

        $activeApp = $operatingSystem->activeClientApps()->first();

        if (!$activeApp) {
            abort(404, 'No active VPN client found for this operating system');
        }

        $appName = strtolower($activeApp->name);


        if ($type === 'setup') {
            $view = "vpn.instructions.setup.{$appName}.setup-{$os}";
        } else {
            $view = "vpn.instructions.connection.{$appName}.add-key-{$os}";
        }

        if (!view()->exists($view)) {
            abort(404, "Instructions not found for {$appName} on {$os}");
        }

        return response()->view($view);
    }
}

