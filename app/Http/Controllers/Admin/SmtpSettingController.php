<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SmtpSetting;

class SmtpSettingController extends Controller
{
    /**
     * Display SMTP Settings page listing.
     */
    public function index(Request $request)
    {
        return view('admin.modules.smtp.list', [
            'q' => $request->q,
            'offset' => $request->offset
        ]);
    }

    /**
     * Fetch SMTP rows for AJAX listing.
     */
    public function listSmtp(Request $request)
    {
        $query = SmtpSetting::query();
        $offset = $request->offset ?? 10;

        if ($request->q) {
            $query->where('mailer', 'like', "%{$request->q}%")
                  ->orWhere('host', 'like', "%{$request->q}%")
                  ->orWhere('username', 'like', "%{$request->q}%");
        }

        $items = $query->orderBy('id', 'desc')->paginate($offset);

        $data = [
            'rows'       => view('admin.modules.smtp.list_rows', ['items' => $items])->render(),
            'items'      => $items,
            'pagination' => view('admin.inc.pagination', ['result' => $items])->render(),
        ];

        return response()->json($data, 200);
    }

    /**
     * Show form for creating new SMTP setting.
     */
    public function create()
    {
        return view('admin.modules.smtp.add', [
            'item' => false
        ]);
    }

    /**
     * Store or update SMTP setting.
     */
    public function store(Request $request)
    {
        $request->validate([
            'mailer'       => 'required|string|max:50',
            'host'         => 'required|string|max:100',
            'port'         => 'required|numeric',
            'username'     => 'required|string|max:100',
            'password'     => 'required|string|max:100',
            'encryption'   => 'nullable|string|max:10',
            'from_address' => 'nullable|email',
            'from_name'    => 'nullable|string|max:100',
        ]);

        if ($request->id) {
            $smtp = SmtpSetting::findOrFail($request->id);
            $smtp->update($request->only([
                'mailer','host','port','username','password',
                'encryption','from_address','from_name'
            ]));
            $message = 'SMTP Setting Updated';
        } else {
            SmtpSetting::create($request->only([
                'mailer','host','port','username','password',
                'encryption','from_address','from_name'
            ]));
            $message = 'SMTP Setting Added';
        }

        return response()->json([
            'success'  => true,
            'message'  => $message,
            'redirect' => route('smtp.index')
        ]);
    }

    /**
     * Show form for editing SMTP setting.
     */
    public function edit($id)
    {
        $item = SmtpSetting::findOrFail($id);
        return view('admin.modules.smtp.add', [
            'item' => $item
        ]);
    }

    /**
     * Delete SMTP setting.
     */
    public function delete(Request $request)
    {
        $smtp = SmtpSetting::findOrFail($request->id);
        $smtp->delete();

        return response()->json([
            'message' => 'Deleted Successfully!',
        ], 200);
    }

    /**
     * Optional: Send test email to verify SMTP works.
     */
    public function test($id)
    {
        $smtp = SmtpSetting::findOrFail($id);

        // dynamically set mail config
        config([
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.host'      => $smtp->host,
            'mail.mailers.smtp.port'      => $smtp->port,
            'mail.mailers.smtp.username'  => $smtp->username,
            'mail.mailers.smtp.password'  => $smtp->password,
            'mail.mailers.smtp.encryption'=> $smtp->encryption,
            'mail.from.address'           => $smtp->from_address,
            'mail.from.name'              => $smtp->from_name,
        ]);

        try {
            \Mail::raw('This is a test email from your SMTP settings.', function ($message) use ($smtp) {
                $message->to($smtp->from_address)
                        ->subject('SMTP Test Mail');
            });

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: '.$e->getMessage()
            ], 500);
        }
    }
}
