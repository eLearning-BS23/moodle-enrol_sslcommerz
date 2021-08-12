# Moodle Enrol Sslcommerz plugin

SSLCOMMERZ is the largest payment gateway aggregator in Bangladesh and a pioneer in the FinTech industry. For more detail about `SSLCOMMERZ` please visit https://www.sslcommerz.com/.

Moodle Enrol Sslcommerz is a Moodle enrollment plugin based on `SSLCOMMERZ` payment gateway that help students to pay with Bangladeshi currency. It supports all Bangladeshi Banks and online mobile transaction.

<p align="center">
<img src="https://i.imgur.com/mYH9uNd.png?1">
</p>


## Features
- Support all Bangladeshi Bank 
- Support All Bangladeshi Mobile Banking
- Easy Integration
- Personalised payment experience
- Secure OTP based access to save cards
- Bi-lingual Support


## Configuration

You can install this plugin from [Moodle plugins directory](https://moodle.org/plugins) or can download from [Github](https://github.com/eLearning-BS23/moodle-enrol_sslcommerz).

You can download zip file and install or you can put file under enrol as sslcommerz

## Plugin Global Settings
### Go to 
```
  Dashboard->Site administration->Plugins->Enrolments->sslcommerz settings
```
- Insert the SSLCOMMERZ api v3 url
  https://sandbox.sslcommerz.com/gwprocess/v3/api.php
- Insert sslcommerz validetion url 
  https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php
- Insert the Store id that provided by sslcommerz
- Insert Store Password provided by sslcommerz
- Done!

Login your sslcommerz account 
```
  My Stores->IPN Settings
```
then update the IPN at HTTP Listener
- http://site-name/enrol/sslcommerz/return.php
<p align="center">
<img src="https://i.imgur.com/DmqB6SW.png?1">
</p>

  

<p align="center">
<img src="https://i.imgur.com/Tc0Bx9p.png">
</p>

<p align="center">
<img src="https://i.imgur.com/4fpLrUh.png?1" width="80%">
</p>

## Enrolment settings: 
- Login as a course adminstrator.
- Enable enrol sslcommerz plugin. 
- Go to your course settings from 

```
  Dashboard->Courses->Course->Users->Enrolment methods settings
```
-  Choose `sslcommerz` from `Add method` option


<p align="center">
<img src="https://i.imgur.com/LCsYjte.png?1" width="40%">
</p>

- Add price and other required informations. 
```
Dashboard->Courses->Course->Users->Enrolment methods->SSLCOMMERZ settings
```
<p align="center">
<img src="https://i.imgur.com/AzKUNpK.png?1">
</p>

- Save changes.
- That's it. and you are done!
- Enjoy the plugin!

