<?php

namespace App\Http\Services\Panel\Admin\Settings\Controller;

use App\Http\Controllers\Controller;
use App\Http\Core\Classes\Notification\WhatsappSender;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TestWhatsappController extends Controller
{
    public function __invoke(Request $request, WhatsappSender $sender): RedirectResponse
    {
        $data = $request->validate([
            'test_phone' => ['required', 'string', 'max:32'],
        ]);

        $message = textByLanguage(
            'رسالة تجريبية من لوحة FleetOS — الاتصال يعمل.',
            'FleetOS panel test message — the connection works.'
        );

        $ok = $sender->send($data['test_phone'], $message, app()->getLocale());

        return back()->with(
            $ok ? 'status' : 'error',
            $ok
                ? textByLanguage('تم إرسال الرسالة التجريبية بنجاح', 'Test message sent successfully')
                : textByLanguage('فشل إرسال الرسالة — تحقق من المفاتيح والجلسة', 'Send failed — check the keys and session')
        );
    }
}
