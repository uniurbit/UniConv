<?php

//This is variable is an example - Just make sure that the urls in the 'idp' config are ok.
$idp_host = env('SAML2_IDP_HOST', 'http://localhost:3000/');

return $settings = array(

    /**
     * If 'useRoutes' is set to true, the package defines five new routes:
     *
     *    Method | URI                      | Name
     *    -------|--------------------------|------------------
     *    POST   | {routesPrefix}/acs       | saml_acs
     *    GET    | {routesPrefix}/login     | saml_login
     *    GET    | {routesPrefix}/logout    | saml_logout
     *    GET    | {routesPrefix}/metadata  | saml_metadata
     *    GET    | {routesPrefix}/sls       | saml_sls
     */
    'useRoutes' => true,

    'routesPrefix' => '/saml2',

    /**
     * which middleware group to use for the saml routes
     * Laravel 5.2 will need a group which includes StartSession
     */
    'routesMiddleware' => ['web'],

    /**
     * Indicates how the parameters will be
     * retrieved from the sls request for signature validation
     */
    'retrieveParametersFromServer' => false,

    /**
     * Where to redirect after logout
     */
    'logoutRoute' => '',

    /**
     * Where to redirect after login if no other option was provided
     */
    'loginRoute' => '',


    /**
     * Where to redirect after login if no other option was provided
     */
    'errorRoute' => '/error',

    /*****
     * One Login Settings
     */

    // If 'strict' is True, then the PHP Toolkit will reject unsigned
    // or unencrypted messages if it expects them signed or encrypted
    // Also will reject the messages if not strictly follow the SAML
    // standard: Destination, NameId, Conditions ... are validated too.
    'strict' => true, //@todo: make this depend on laravel config

    // Enable debug mode (to print errors)
    'debug' => env('APP_DEBUG', true),

    // If 'proxyVars' is True, then the Saml lib will trust proxy headers
    // e.g X-Forwarded-Proto / HTTP_X_FORWARDED_PROTO. This is useful if
    // your application is running behind a load balancer which terminates
    // SSL.
    'proxyVars' => false,

    // Service Provider Data that we are deploying
    'sp' => array(
        
        // Specifies constraints on the name identifier to be used to
        // represent the requested subject.
        // Take a look on lib/Saml2/Constants.php to see the NameIdFormat supported
        'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent',

        // Usually x509cert and privateKey of the SP are provided by files placed at
        // the certs folder. But we can also provide them with the following parameters
        'x509cert' => env('SAML2_SP_x509',''),
        'privateKey' => env('SAML2_SP_PRIVATEKEY',''),

        // Identifier (URI) of the SP entity.
        // Leave blank to use the 'saml_metadata' route.
        'entityId' => env('SAML2_SP_ENTITYID',''),

        // Specifies info about where and how the <AuthnResponse> message MUST be
        // returned to the requester, in this case our SP.
        'assertionConsumerService' => array(
            // URL Location where the <Response> from the IdP will be returned,
            // using HTTP-POST binding.
            // Leave blank to use the 'saml_acs' route
            'url' => '',
        ),
        // Specifies info about where and how the <Logout Response> message MUST be
        // returned to the requester, in this case our SP.
        // Remove this part to not include any URL Location in the metadata.
        'singleLogoutService' => array(
            // URL Location where the <Response> from the IdP will be returned,
            // using HTTP-Redirect binding.
            // Leave blank to use the 'saml_sls' route
            'url' => '',        
        ),
    ),

    // Identity Provider Data that we want connect with our SP
    'idp' => array(
        // Identifier of the IdP entity  (must be a URI)
        'entityId' => env('SAML2_IDP_ENTITYID', $idp_host . 'idp'), // shibboleth

        // SSO endpoint info of the IdP. (Authentication Request protocol)
        'singleSignOnService' => array(
            // URL Target of the IdP where the SP will send the Authentication Request Message,
            // using HTTP-Redirect binding.
            'url' => $idp_host . 'idp/profile/SAML2/Redirect/SSO',

            // HTTP-POST binding only
            
        ),
        // SLO endpoint info of the IdP.
        'singleLogoutService' => array(
            // URL Location of the IdP where the SP will send the SLO Request,
            // using HTTP-Redirect binding.
            'url' => $idp_host . 'idp/profile/Logout',
        ),


        // Public x509 certificate of the IdP del fake-sso-idp
        'x509cert' => env('SAML2_IDP_x509', 'MIIDQjCCAiqgAwIBAgIJAMJmgNUZPACuMA0GCSqGSIb3DQEBBQUAMB8xCzAJBgNV
        BAYTAlNFMRAwDgYDVQQDEwdpZHAuY29tMB4XDTE3MDMyNDEyNDkwNVoXDTI3MDMy
        MjEyNDkwNVowHzELMAkGA1UEBhMCU0UxEDAOBgNVBAMTB2lkcC5jb20wggEiMA0G
        CSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQDMcmjv50Q5wopD/2vK9cafOz692OuB
        gokJVdEi2B2/gtkjP14oknCf1StNiql2EUnwRqZuKvcso57dqhWELvy0ljIMyoXK
        wfDz7+hTGxoumr6n5o+sOQ5qhVukuwFruI87isH5cPVVURITjtb9RdHnbhoBMCzG
        HR4azwF9ADf1AL1J+lOgnjXljyRL0rpBlute1IV9Q6aX2dF5Q6ouJizBfFtnVU+S
        bHI19eNhWTQ3TCIwcLjjYLRMbZ9Euqqh20zi4p+rdqzgWhSL/uVT0a+ND9vvc6DN
        EUfyZkMRsqgbYXXJQcSK5LXg4pVJAoUibstnNAEw7cdwjWN0R2kebWXhAgMBAAGj
        gYAwfjAdBgNVHQ4EFgQUAb6YtaOr4RY+euaOhghGTo2CKNIwTwYDVR0jBEgwRoAU
        Ab6YtaOr4RY+euaOhghGTo2CKNKhI6QhMB8xCzAJBgNVBAYTAlNFMRAwDgYDVQQD
        EwdpZHAuY29tggkAwmaA1Rk8AK4wDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQUF
        AAOCAQEAlcJmU1aqx1o71/M7/jvX48KFdAPqatmTSrSBD3wmaAMV68DI899GapJH
        SxPTsYOX5VEX6yV/51FMcLaqS06qMg8aHlJ7XcsewwqFm//PUeoQisVpY4uIp7KB
        uNXtWHN7Jx2HixoulCmPNsLDWU2yLhCVQBJoLgAU1Y2WlDbVI+KodramB0Qp+VOv
        ZsVZAju6hN0QySs9NSJoqNac9elHASMvve+T/VPi7+7oyu1cncs1KsE3LwPJa58w
        ePLiLh5Y1GgCIX/HnYpZDeQEPRFnJ7SLI2eQsmOsXrdM+1izqNROPSSAforFi7ds
        SakhS/fgBX+i0bXL816kY/RzbMnQTg=='),
        /*
         *  Instead of use the whole x509cert you can use a fingerprint
         *  (openssl x509 -noout -fingerprint -in "idp.crt" to generate it)
         */
        // 'certFingerprint' => '',
    ),



    /***
     *
     *  OneLogin advanced settings
     *
     *
     */
    // Security settings
    'security' => array(

        /** signatures and encryptions offered */

        // Indicates that the nameID of the <samlp:logoutRequest> sent by this SP
        // will be encrypted.
        'nameIdEncrypted' => false,

        // Indicates whether the <samlp:AuthnRequest> messages sent by this SP
        // will be signed.              [The Metadata of the SP will offer this info]
        'authnRequestsSigned' => false,

        // Indicates whether the <samlp:logoutRequest> messages sent by this SP
        // will be signed.
        'logoutRequestSigned' => false,

        // Indicates whether the <samlp:logoutResponse> messages sent by this SP
        // will be signed.
        'logoutResponseSigned' => false,

        /* Sign the Metadata
         False || True (use sp certs) || array (
                                                    keyFileName => 'metadata.key',
                                                    certFileName => 'metadata.crt'
                                                )
        */
        'signMetadata' => false,


        /** signatures and encryptions required **/

        // Indicates a requirement for the <samlp:Response>, <samlp:LogoutRequest> and
        // <samlp:LogoutResponse> elements received by this SP to be signed.
        'wantMessagesSigned' => false,

        // Indicates a requirement for the <saml:Assertion> elements received by
        // this SP to be signed.        [The Metadata of the SP will offer this info]
        'wantAssertionsSigned' => false,

        // Indicates a requirement for the NameID received by
        // this SP to be encrypted.
        'wantNameIdEncrypted' => false,

        'wantAssertionsEncrypted' => false, //true
        // Authentication context.
        // Set to false and no AuthContext will be sent in the AuthNRequest,
        // Set true or don't present thi parameter and you will get an AuthContext 'exact' 'urn:oasis:names:tc:SAML:2.0:ac:classes:PasswordProtectedTransport'
        // Set an array with the possible auth context values: array ('urn:oasis:names:tc:SAML:2.0:ac:classes:Password', 'urn:oasis:names:tc:SAML:2.0:ac:classes:X509'),
        'requestedAuthnContext' => false,        

        'signatureAlgorithm' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
      
        'digestAlgorithm' => 'http://www.w3.org/2001/04/xmlenc#sha256',
    ),

    // Contact information template, it is recommended to suply a technical and support contacts
    'contactPerson' => array(
        'technical' => array(
            'givenName' => 'name',
            'emailAddress' => 'no@reply.com'
        ),
        'support' => array(
            'givenName' => 'Support',
            'emailAddress' => 'no@reply.com'
        ),
    ),

    // Organization information template, the info in en_US lang is recomended, add more if required
    'organization' => array(
        'en-US' => array(
            'name' => 'Name',
            'displayname' => 'Display Name',
            'url' => 'http://url'
        ),
    ),

);
