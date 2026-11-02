{{-- Wrapper for MEC's booking emails (verification, received, confirmed, admin…). Email clients ignore
     stylesheets, so everything is inline, laid out in tables, with web-safe fonts and a PNG logo
     (Gmail and Outlook drop SVG). Colours are the theme's: blue-dark, blue, blue-light, charcoal, yellow. --}}
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#E9F0F3;margin:0;padding:0;">
  <tr>
    <td align="center" style="padding:32px 12px;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;background-color:#FFFFFF;border-radius:12px;overflow:hidden;">
        <tr>
          <td style="background-color:#003852;padding:24px 32px;">
            <a href="{{ home_url('/') }}"><img src="{{ $logo }}" width="174" alt="{{ get_bloginfo('name') }}" style="display:block;border:0;height:auto;"></a>
          </td>
        </tr>
        <tr>
          <td style="padding:32px;font-family:Arial,Helvetica,sans-serif;font-size:16px;line-height:24px;color:#363636;">
            {!! $content !!}
          </td>
        </tr>
        <tr>
          <td style="background-color:#E9F0F3;padding:16px 32px;font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:20px;color:#003852;">
            <a href="{{ home_url('/') }}" style="color:#003852;text-decoration:underline;">{{ preg_replace('#^https?://#', '', untrailingslashit(home_url())) }}</a>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
