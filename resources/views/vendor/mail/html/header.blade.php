@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
@if (trim($slot) === 'Laravel')
<img src="https://laravel.com/img/notification-logo-v2.1.png" class="logo" alt="Laravel Logo">
@else
<table cellpadding="0" cellspacing="0" border="0" style="margin: 0 auto;">
    <tr>
        <td style="width: 44px; height: 44px; background-color: #4f46e5; border-radius: 10px; text-align: center; vertical-align: middle;">
            <span style="color: #ffffff; font-size: 22px; font-weight: 700; line-height: 44px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">C</span>
        </td>
        <td style="width: 12px;"></td>
        <td style="vertical-align: middle;">
            <span style="font-size: 20px; font-weight: 700; color: #1f2937; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
                {{ $slot }}
            </span>
        </td>
    </tr>
</table>
@endif
</a>
</td>
</tr>
