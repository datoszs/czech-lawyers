<g:applyLayout name="placeholder">
    <head>
        <title>Chyba</title>
    </head>
    <content tag="content">
        <h1>Chyba</h1>
        <g:if env="development">
            <g:if test="${Throwable.isInstance(exception)}">
                <g:renderException exception="${exception}" />
            </g:if>
            <g:elseif test="${request.getAttribute('javax.servlet.error.exception')}">
                <g:renderException exception="${request.getAttribute('javax.servlet.error.exception')}" />
            </g:elseif>
            <g:else>
                <ul class="errors">
                    <li>Exception: ${exception}</li>
                    <li>Message: ${message}</li>
                    <li>Path: ${path}</li>
                </ul>
            </g:else>
        </g:if>
        <g:else>
            <p>Nastala neočekávaná chyba systému, prosíme, kontaktujte nás.</p>
        </g:else>
    </content>
</g:applyLayout>
