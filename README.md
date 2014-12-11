Pour la configuration voir le fichier DreamStore/app/config/parameters.yml.dist.
Ne pas oublier de faire un composer update.
Ensuite si vous voulez avoir du designe sue la page de connection copier coller ce code a la route suivante :

DreamStore/vendor/hwi/oauth-bundle/HWI/Bundle/Resources/views/Connect/login.html.twig

<code>
{% extends '::base.html.twig' %}
{%block body%}
{% block hwi_oauth_content %}
    {% if error is defined and error %}
        <span>{{ error }}</span>
    {% endif %}
    {% for owner in hwi_oauth_resource_owners() %}
    <a href="{{ hwi_oauth_login_url(owner) }}" class="link_login btn btn-default btn-lg">{{ owner | trans({}, 'HWIOAuthBundle') }}</a>
    {% endfor %}
{% endblock hwi_oauth_content %}

{%endblock%}
</code>
