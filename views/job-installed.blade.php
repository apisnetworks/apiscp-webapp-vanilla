@component('email.indicator', ['status' => 'success'])
	Your application is installed.
@endcomponent

@component('mail::message')
{{-- Body --}}
# Howdy!

{{ $appname }} has been successfully installed on {{ $uri }}!

## Admin panel
You can access the panel at [{{$proto}}{{$uri}}/{{ \Module\Support\Webapps\App\Type\Vanilla\Handler::ADMIN_PATH }}]({{$proto}}{{$uri}}/{{ \Module\Support\Webapps\App\Type\Vanilla\Handler::ADMIN_PATH }}) using the following information:

**Login**: <code>{{ $login }}</code><br/>
**Password**: <code>{{ str_replace('@', '\\@', $password) }}</code>

---

Security is important with any application, so extra steps are taken to reduce
the risk of hackers. By default **Maximum** Fortification is enabled. This will
prevent additional plugins from being loaded into Vanilla.

To enable or modify plugins, change the Fortification mode to **Minimum**.

Here's how to do it:

1. Visit **Web** > **Web Apps** in {{PANEL_BRAND}}
2. Select {{ $appname }} installed under **{{$uri}}**
3. Select **Fortification (MIN)** under _Actions_

You can learn more about [Fortification technology]({{MISC_KB_BASE}}/control-panel/understanding-fortification/) within the [knowledgebase]({{MISC_KB_BASE}}).

@include('email.webapps.common-footer')
@endcomponent