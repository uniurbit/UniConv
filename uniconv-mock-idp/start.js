const { create } = require('fake-sso-idp')
const app = create({
  serviceProvider: {
    destination: 'http://127.0.0.1/saml2/acs',
    metadata: 'http://127.0.0.1/saml2/metadata' //'http://127.0.0.1/saml2/metadata' //'http://127.0.0.1:8000/saml2/metadata/' per PEO
  },  
  users: [
    {
      id: 'test1',
      name: 'SuperAdmin Enrico Oliva',
      username: 'enrico',
      password: 'pwd',
      attributes: {
        pisa_id: {
          format: 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
          value: 'super-admin',
          type: 'xs:string'
        },
        'nome':{
          format: 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
          value: 'Enrico',
          type: 'xs:string'
        },
        'cognome':{
          format: 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
          value: 'Oliva',
          type: 'xs:string'
        },
       'urn:oid:2.16.840.1.113730.3.1.241':{
          format: 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
          value: 'Enrico Oliva',
          type: 'xs:string'
        },
        'urn:oid:1.3.6.1.4.1.4203.666.11.11.1.0':{
          format: 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
          value: '2222222222222222',
          type: 'xs:string'
        },
        'urn:oid:0.9.2342.19200300.100.1.3':{
          format:  'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
          value: 'enrico.oliva@uniurb.it',
          type: 'xs:string'
        },
        'urn:oid:1.3.6.1.4.1.5923.1.1.1.9':{
          format:  'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
          value:  'staff@uniurb.it',
          type: 'xs:string'
        },
       
      }
    },   
    {
      id: 'test2',
      name: 'Operatore Uff.',
      username: 'enrico',
      password: 'pwd',
      attributes: {
        pisa_id: {
          format: 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
          value: 'admin',
          type: 'xs:string'
        },
       'urn:oid:2.16.840.1.113730.3.1.241':{
          format: 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
          value: 'Lucia Bedini',
          type: 'xs:string'
        },
        'urn:oid:1.3.6.1.4.1.4203.666.11.11.1.0':{
          format: 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
          value: '2222222222222222',
          type: 'xs:string'
        },
        'urn:oid:0.9.2342.19200300.100.1.3':{
          format:  'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
          value: 'lucia.bedini@uniurb.it',
          type: 'xs:string'
        },
        'urn:oid:1.3.6.1.4.1.5923.1.1.1.9':{
          format:  'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
          value:  'staff@uniurb.it',
          type: 'xs:string'
        },
      }
    },
    {
      id: 'test3',
      name: 'Operatore Uff. Contabilità',
      username: 'mirella',
      password: 'pwd',
      attributes: {
        pisa_id: {
          format: 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
          value: 'admin',
          type: 'xs:string'
        },
       'urn:oid:2.16.840.1.113730.3.1.241':{
          format: 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
          value: 'Mirella Guglielmi',
          type: 'xs:string'
        },
        'urn:oid:1.3.6.1.4.1.4203.666.11.11.1.0':{
          format: 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
          value: '2222222222222222',
          type: 'xs:string'
        },
        'urn:oid:0.9.2342.19200300.100.1.3':{
          format:  'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
          value: 'mirella.guglielmi@uniurb.it',
          type: 'xs:string'
        },
        'urn:oid:1.3.6.1.4.1.5923.1.1.1.9':{
          format:  'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
          value:  'staff@uniurb.it',
          type: 'xs:string'
        },
      }
    },
    {
      id: 'test4',
      name: 'Operatore Uff. Bilancio',
      username: 'anna',
      password: 'pwd',
      attributes: {
        pisa_id: {
          format: 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
          value: 'op_uff_bilancio',
          type: 'xs:string'
        },
       'urn:oid:2.16.840.1.113730.3.1.241':{
          format: 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
          value: 'Anna Valentini',
          type: 'xs:string'
        },
        'urn:oid:1.3.6.1.4.1.4203.666.11.11.1.0':{
          format: 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
          value: '2222222222222222',
          type: 'xs:string'
        },
        'urn:oid:0.9.2342.19200300.100.1.3':{
          format:  'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
          value: 'anna.valentini@uniurb.it',
          type: 'xs:string'
        },
        'urn:oid:1.3.6.1.4.1.5923.1.1.1.9':{
          format:  'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
          value:  'staff@uniurb.it',
          type: 'xs:string'
        },
      }
    },
    {
      id: 'test6',
      name: 'Operatore Uff.',
      username: 'anna',
      password: 'pwd',
      attributes: {
        pisa_id: {
          format: 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
          value: '',
          type: 'xs:string'
        },
       'urn:oid:2.16.840.1.113730.3.1.241':{
          format: 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
          value: 'Maria Gargano',
          type: 'xs:string'
        },
        'urn:oid:1.3.6.1.4.1.4203.666.11.11.1.0':{
          format: 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
          value: '2222222222222223',
          type: 'xs:string'
        },
        'urn:oid:0.9.2342.19200300.100.1.3':{
          format:  'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
          value: 'maria.gargano@uniurb.it',
          type: 'xs:string'
        },
        'urn:oid:1.3.6.1.4.1.5923.1.1.1.9':{
          format:  'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
          value:  'staff@uniurb.it',
          type: 'xs:string'
        },
      }
    },
    {
      id: 'test5',
      name: 'Responsabile Plesso',
      username: 'JOSEPH',
      password: 'pwd',
      attributes: {
        pisa_id: {
          format: 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
          value: 'admin',
          type: 'xs:string'
        },
       'urn:oid:2.16.840.1.113730.3.1.241':{
          format: 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
          value: 'JOSEPH',
          type: 'xs:string'
        },
        'urn:oid:1.3.6.1.4.1.4203.666.11.11.1.0':{
          format: 'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
          value: '2222222222222222',
          type: 'xs:string'
        },
        'urn:oid:0.9.2342.19200300.100.1.3':{
          format:  'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
          value: 'joseph.fontana@uniurb.it',
          type: 'xs:string'
        },
        'urn:oid:1.3.6.1.4.1.5923.1.1.1.9':{
          format:  'urn:oasis:names:tc:SAML:2.0:attrname-format:uri',
          value:  'staff@uniurb.it',
          type: 'xs:string'
        },
      }
    },
    
  ]
})

app.listen(7000)