<?php
namespace App\DataFixtures\Providers\Modules;

class Notes{

    const KEY_NAME      = 'title';
    const KEY_BODY      = 'body';
    const KEY_CATEGORY_NAME  = 'category';

    const NOTE_DIET_DAILY_CONSUMPTION = [
        self::KEY_NAME           => 'Daily consumption',
        self::KEY_BODY           => '<ul class="left" style="text-align: left;" data-mce-style="text-align: left;"><li class="left" style="text-align: left;" data-mce-style="text-align: left;"><strong class="left" style="text-align: left;" data-mce-style="text-align: left;">80kg / 185cm / training. 5x/week.</strong></li><li class="left" style="text-align: left;" data-mce-style="text-align: left;"><strong class="left" style="text-align: left;" data-mce-style="text-align: left;">Calories</strong>: 2931</li><li class="left" style="text-align: left;" data-mce-style="text-align: left;"><strong class="left" style="text-align: left;" data-mce-style="text-align: left;">Protein</strong>: 175</li><li class="left" style="text-align: left;" data-mce-style="text-align: left;"><strong class="left" style="text-align: left;" data-mce-style="text-align: left;">Fats</strong>: ~95</li><li class="left" style="text-align: left;" data-mce-style="text-align: left;"><strong class="left" style="text-align: left;" data-mce-style="text-align: left;">Carbohydrates</strong>: ~400</li></ul>',
        self::KEY_CATEGORY_NAME  => 'Diet',
    ];

    const NOTE_CAR_SPARE_MATERIALS = [
        self::KEY_NAME           => 'Spare materials',
        self::KEY_BODY           => '<ul><li><strong>Car oil</strong>: SHELL 10W40 HELIX HX7</li></ul>',
        self::KEY_CATEGORY_NAME  => 'Car',
    ];

    const NOTE_FINANCES_POLAND_ROCK_FESTIVAL_2019_SUMMARY = [
        self::KEY_NAME           => '[01-08-2019] Poland Rock Festival 2019',
        self::KEY_BODY           => '<ul class="left" style="text-align: left;" data-mce-style="text-align: left;"><li class="left" style="text-align: left;" data-mce-style="text-align: left;">Gasoline: 535 PLN</li><li class="left" style="text-align: left;" data-mce-style="text-align: left;">Food and booze: 124 PLN</li><li class="left" style="text-align: left;" data-mce-style="text-align: left;">Highway: 76 PLN</li><li class="left" style="text-align: left;" data-mce-style="text-align: left;">Parking lot: 80 PLN<br></li></ul>Saved: 275 PLN<br class="left" style="text-align: left;" data-mce-style="text-align: left;"><br class="left" style="text-align: left;" data-mce-style="text-align: left;"><strong>Summary ~:</strong> 540 PLN',
        self::KEY_CATEGORY_NAME  => 'Finances',
    ];

    const NOTE_FINANCES_WORKING_ABROAD_RESUPPLY = [
        self::KEY_NAME           => '[05-08-2019] Working abroad - resupply',
        self::KEY_BODY           => '<ul class="left" style="text-align: left;" data-mce-style="text-align: left;"><li class="left" style="text-align: left;" data-mce-style="text-align: left;">Food: 550 PLN</li><li class="left" style="text-align: left;" data-mce-style="text-align: left;">Car oil: 50 PLN</li><li class="left" style="text-align: left;" data-mce-style="text-align: left;">Meat: 80 PLN</li><li class="left" style="text-align: left;" data-mce-style="text-align: left;">Medicaments: 60 PLN</li><li class="left" style="text-align: left;" data-mce-style="text-align: left;">Suplements: 203 PLN</li><li class="left" style="text-align: left;" data-mce-style="text-align: left;">Perfumes: 65 PLN</li><li class="left" style="text-align: left;" data-mce-style="text-align: left;">Yerba Mate: 53 PLN</li></ul><br class="left" style="text-align: left;" data-mce-style="text-align: left;"><strong class="left" style="text-align: left;" data-mce-style="text-align: left;">Summary</strong>: ~1000 PLN',
        self::KEY_CATEGORY_NAME  => 'Finances',
    ];

    const NOTE_GOALS_CATEGORY_B_MOTORBIKES = [
        self::KEY_NAME           => 'Motorbikes on category B',
        self::KEY_BODY           => '<p>Interesting models:</p><ul><li lang="PL-PL" xml:lang="PL-PL"><span lang="PL-PL" xml:lang="PL-PL" data-contrast="none">Yamaha&nbsp;Virago&nbsp;125</span>&nbsp;</li><li lang="PL-PL" xml:lang="PL-PL"><span lang="PL-PL" xml:lang="PL-PL" data-contrast="auto">Honda&nbsp;Rebel&nbsp;CA125</span>&nbsp;</li><li lang="PL-PL" xml:lang="PL-PL"><span lang="PL-PL" xml:lang="PL-PL" data-contrast="none">Honda&nbsp;Shadow&nbsp;125</span>&nbsp;</li></ul><p>Website with more models:</p><ul><li lang="PL-PL" xml:lang="PL-PL"><a href="https://www.motocykle125.pl/lista-prawo-jazdy-b/" target="_blank" rel="noopener noreferrer" data-mce-href="https://www.motocykle125.pl/lista-prawo-jazdy-b/"><span lang="PL-PL" xml:lang="PL-PL" data-contrast="auto">https://www.motocykle125.pl/lista-prawo-jazdy-b/</span></a></li></ul><p><br></p>',
        self::KEY_CATEGORY_NAME  => 'Goals',
    ];

    const NOTE_GOALS_TATTOO = [
        self::KEY_NAME           => 'Tattoo',
        self::KEY_BODY           => '<p lang="PL-PL" xml:lang="PL-PL">Good tattoo studios to check:</p><ul><li lang="PL-PL" xml:lang="PL-PL"><a href="http://arttattoo.pl/lukasz-galeria/" target="_blank" rel="noopener noreferrer" data-mce-href="http://arttattoo.pl/lukasz-galeria/"><span lang="PL-PL" xml:lang="PL-PL" data-contrast="auto">http://arttattoo.pl/lukasz-galeria/</span></a>&nbsp;</li><li lang="PL-PL" xml:lang="PL-PL"><a href="http://totutattoo.pl/" target="_blank" rel="noopener noreferrer" data-mce-href="http://totutattoo.pl/"><span lang="PL-PL" xml:lang="PL-PL" data-contrast="auto">http://totutattoo.pl/</span></a>&nbsp;</li><li lang="PL-PL" xml:lang="PL-PL"><a href="https://inkstinct.co/studio/inkmania-studio-tattoo-poznan" target="_blank" rel="noopener noreferrer" data-mce-href="https://inkstinct.co/studio/inkmania-studio-tattoo-poznan"><span lang="PL-PL" xml:lang="PL-PL" data-contrast="auto">https://inkstinct.co/studio/inkmania-studio-tattoo-poznan</span></a>&nbsp;</li><li lang="PL-PL" xml:lang="PL-PL"><a href="http://www.empiretattoo.pl/" target="_blank" rel="noopener noreferrer" data-mce-href="http://www.empiretattoo.pl/"><span lang="PL-PL" xml:lang="PL-PL" data-contrast="auto">http://www.empiretattoo.pl/</span></a>&nbsp;</li><li lang="PL-PL" xml:lang="PL-PL"><a href="https://www.instagram.com/tattooboompoznan/" target="_blank" rel="noopener noreferrer" data-mce-href="https://www.instagram.com/tattooboompoznan/"><span lang="PL-PL" xml:lang="PL-PL" data-contrast="auto">https://www.instagram.com/tattooboompoznan/</span></a><span lang="PL-PL" xml:lang="PL-PL" data-contrast="auto">&nbsp;</span>&nbsp;</li><li lang="PL-PL" xml:lang="PL-PL"><a href="https://www.dwasledzie.com/" target="_blank" rel="noopener noreferrer" data-mce-href="https://www.dwasledzie.com/"><span lang="PL-PL" xml:lang="PL-PL" data-contrast="auto">https://www.dwasledzie.com/</span></a><span lang="PL-PL" xml:lang="PL-PL" data-contrast="auto">&nbsp;</span>&nbsp;</li><li lang="PL-PL" xml:lang="PL-PL"><a href="https://www.facebook.com/pg/Montazownia/photos/?ref=page_internal" target="_blank" rel="noopener noreferrer" data-mce-href="https://www.facebook.com/pg/Montazownia/photos/?ref=page_internal"><span lang="PL-PL" xml:lang="PL-PL" data-contrast="auto">https://www.facebook.com/pg/Montazownia/photos/?ref=page_internal</span></a><span lang="PL-PL" xml:lang="PL-PL" data-contrast="auto">&nbsp;</span>&nbsp;</li></ul><p><br></p>',
        self::KEY_CATEGORY_NAME  => 'Goals',
    ];

    const CATEGORY_APPLICATIONS_LICENCE_KEY = [
        self::KEY_NAME           => 'Licence key',
        self::KEY_BODY           => '<span class="left" style="text-align: left;" data-mce-style="text-align: left;">CATF44LT7C-eyasdasdasdsad-09=-00hih7sdfMC0wMS0wOCJ9LHsiY29kZSI6IkRDIiwicGFpZFVwVG8iOiIyMDIwLTAxLTA4In0seyJjb2RlIjoiUlNVIiwicGFpZFVwVG8iOiIyMDIwLTAxLTA4In1dLCJoYXNoIjoiMTE1MzA4ODUvMCIsImdyYWNlUGVyaW9kRGF5cyI6MCwiYXV0b1Byb2xvbmdhdGVkIjpmYWxzZSwiaXNBdXRvUHJvbG9uZ2F0ZWQiOmZhbHNlfQ==-//[[-o9jfsd98fjsdsBZLL+H88k449OQC56NsqU0fwb6wMAX1Di+CK5HS46DuOD1E68HPiTqREdn8DzrLVAoMkJReaH30RaIDLwUI8GEFifDcCYE5RbpE5ApNJ8mcUJr8oA1nrjY9IzZCgrSBFr4GAOLqSfXH+1UJ3K8UPqGh8nThomnKW9Jvv9pA7HIH/KrNm2RLV/aNMHWO8Q44A8ToXm7g5FS2lW903URPQ0KFgxT11w/KL81UkHm6yUXCfsdfsdf14s5f42sd73msduf8&amp;&amp;Mjk0NloXDTIwMTEwMjEyMjk0NlowaDELMAkGA1UEBhMCQ1oxDjAMBgNVBAgMBU51c2xlMQ8wDQYDVQQHDAZQcmFndWUxGTAXBgNVBAoMEEpldEJyYWlucyBzLnIuby4xHTAbBgNVBAMMFHByb2QzeS1mcm9tLTIwMTgxMTAxMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAxcQkq+zdxlR2mmRYBPzGbUNdMN6OaXiXzxIWtMEkrJMO/5oUfQJbLLuMSMK0QHFmaI37WShyxZcfRCidwXjot4zmNBKnlyHodDij/78TmVqFl8nOeD5+07B8VEaIu7c3E1N+e1doC6wht4I4+IEmtsPAdoaj5WCQVQbrI8KeT8M9VcBIWX7fD0fhexfg3ZRt0xqwMcXGNp3DdJHiO0rCdU+Itv7EmtnSVq9jBG1usMSFvMowR25mju2JcPFp1+I4ZI+FqgR8gyG8oiNDyNEoAbsR3lOpI7grUYSvkB/xsdfsf[][df=-0*&amp;(&amp;wOFcPzmbjcxNDuGoOUIP+2h1R75Lecswb7ru2LWWSUMtXVKQzChLNPn/72W0k+oI056tgiwuG7M49LXp4zQVlQnFmWU1wwGvVhq5R63Rpjx1zjGUhcXgayu7+9zMUW596Lbomsg8qVve6euqsrFicYkIIuUu4zYPndJwfe0YkS5nY72SHnNdbPhEnN8wcB2Kz+OIG0lih3yz5EqFhld03bGp222ZQCIghCTVL6QBNadGsiN/lWLl4JdR3lJkZzlpFdiHijoVRdWeSWqM4y0t23c92HXKrgppoSV18XMxrWVdoSM3nuMHwxGhFyde05OdDtLpCv+jlWf5REAHHA201pAU6bJSZINyHDUTB+Beo28rRXSwSh3OUIvYwKNVeoBY+KwOJ7WnuTCUq1meE6GkKc4sdfsf[p0-9)*(**GGU*</span>',
        self::KEY_CATEGORY_NAME  => 'Keys',
    ];

    const CATEGORY_SERVICES_FESTNETZ = [
        self::KEY_NAME           => 'Festnetz',
        self::KEY_BODY           => '<p style="border: 0px; font-variant-numeric: inherit; font-variant-east-asian: inherit; font-stretch: inherit; line-height: 1.375; margin: 18px 0px 0px; padding: 0px; vertical-align: baseline; background-color: rgb(255, 255, 255);"><font color="#404040" face="Helmet, Freesans, Helvetica, Arial, sans-serif"><strong>Fetnetz comparison:</strong> https://www.preis24.de/festnetz-und-internet/</font><br></p><br>',
        self::KEY_CATEGORY_NAME  => 'Services',
    ];

    const CATEGORY_SERVICES_ADAC = [
        self::KEY_NAME           => 'ADAC',
        self::KEY_BODY           => "The ADAC is Germany's and Europe's largest automobile club, with more than 18 million members in May 2012. It was founded on May 24, 1903 as \"Deutsche Motorradfahrer-Vereinigung\" and was renamed in 1911. Today it is still the largest motorcyclist association in the world with 1.5 million members.",
        self::KEY_CATEGORY_NAME  => 'Services',
    ];

    const CATEGORY_SERVICES_PHONE_ACCOUNT_DATA = [
        self::KEY_NAME           => 'Monthly payments - contract information',
        self::KEY_BODY           => '<p style="border: 0px; font-variant-numeric: inherit; font-variant-east-asian: inherit; font-stretch: inherit; line-height: 1.375; margin: 18px 0px 0px; padding: 0px; vertical-align: baseline; background-color: rgb(255, 255, 255);"><font color="#404040" face="Helmet, Freesans, Helvetica, Arial, sans-serif"><b>Mobile:&nbsp;</b>15<br><strong>Internet 30Gb: </strong>15<br><strong>Contract duration</strong>: 2 years<br><strong>Discount duration</strong>: 6 months<br><strong>Discount</strong> <strong>amount&#65279;:&nbsp;</strong>100%<br><strong>Contract signing</strong> <strong>city</strong>: Kassell&#65279;<br><br></font></p><br>',
        self::KEY_CATEGORY_NAME  => 'Phone',
    ];

    const CATEGORY_SERVICES_BANKING_REVOLUT= [
        self::KEY_NAME           => 'Revolut',
        self::KEY_BODY           => '<ul class="left" style="text-align: left;" data-mce-style="text-align: left;"><li class="left" style="text-align: left;" data-mce-style="text-align: left;"><p style="border: 0px; color: rgb(64, 64, 64); font-variant-numeric: inherit; font-variant-east-asian: inherit; font-stretch: inherit; font-family: Helmet, Freesans, Helvetica, Arial, sans-serif; font-size: 1rem; line-height: 1.375; margin: 18px 0px 0px; padding: 0px; vertical-align: baseline; background-color: rgb(255, 255, 255);">Revolut offers a range of digital banking services in a mobile app targeted at young tech-savvy users, including:</p><ul class="story-body__unordered-list" style="border: 0px; color: rgb(64, 64, 64); font-variant-numeric: inherit; font-variant-east-asian: inherit; font-stretch: inherit; font-family: Helmet, Freesans, Helvetica, Arial, sans-serif; font-size: 14px; line-height: inherit; margin: 18px 0px 0px; padding: 0px; vertical-align: baseline; list-style: none; background-color: rgb(255, 255, 255);"><li class="story-body__list-item" style="border: 0px; color: inherit; font-style: inherit; font-variant: inherit; font-stretch: inherit; font-family: inherit; font-size: 1rem; font-weight: inherit; letter-spacing: inherit; line-height: 1.375; margin: 18px 0px 0px 20px; padding: 0px 0px 0px 4px; vertical-align: baseline; list-style: square outside;">Transferring money abroad in 29 currencies</li><li class="story-body__list-item" style="border: 0px; color: inherit; font-style: inherit; font-variant: inherit; font-stretch: inherit; font-family: inherit; font-size: 1rem; font-weight: inherit; letter-spacing: inherit; line-height: 1.375; margin: 18px 0px 0px 20px; padding: 0px 0px 0px 4px; vertical-align: baseline; list-style: square outside;">A pre-paid debit card that enables cash machine withdrawals in 120 countries</li><li class="story-body__list-item" style="border: 0px; color: inherit; font-style: inherit; font-variant: inherit; font-stretch: inherit; font-family: inherit; font-size: 1rem; font-weight: inherit; letter-spacing: inherit; line-height: 1.375; margin: 18px 0px 0px 20px; padding: 0px 0px 0px 4px; vertical-align: baseline; list-style: square outside;">A crypto-currency exchange allowing users to convert currencies into Bitcoin, Litecoin, Ethereum, Bitcoin Cash or XRP</li><li class="story-body__list-item" style="border: 0px; color: inherit; font-style: inherit; font-variant: inherit; font-stretch: inherit; font-family: inherit; font-size: 1rem; font-weight: inherit; letter-spacing: inherit; line-height: 1.375; margin: 18px 0px 0px 20px; padding: 0px 0px 0px 4px; vertical-align: baseline; list-style: square outside;">Vaults for budgeting and saving money</li><li class="story-body__list-item" style="border: 0px; color: inherit; font-style: inherit; font-variant: inherit; font-stretch: inherit; font-family: inherit; font-size: 1rem; font-weight: inherit; letter-spacing: inherit; line-height: 1.375; margin: 18px 0px 0px 20px; padding: 0px 0px 0px 4px; vertical-align: baseline; list-style: square outside;">Mobile phone and overseas medical insurance</li></ul><p style="border: 0px; color: rgb(64, 64, 64); font-variant-numeric: inherit; font-variant-east-asian: inherit; font-stretch: inherit; font-family: Helmet, Freesans, Helvetica, Arial, sans-serif; font-size: 1rem; line-height: 1.375; margin: 18px 0px 0px; padding: 0px; vertical-align: baseline; background-color: rgb(255, 255, 255);">With standard accounts, users get a free UK current account and a free euro IBAN account. There are no fees on exchanging in 24 currencies, up to £5,000 a month, and you can withdraw up to £200 a month from cash machines.</p><p style="border: 0px; color: rgb(64, 64, 64); font-variant-numeric: inherit; font-variant-east-asian: inherit; font-stretch: inherit; font-family: Helmet, Freesans, Helvetica, Arial, sans-serif; font-size: 1rem; line-height: 1.375; margin: 18px 0px 0px; padding: 0px; vertical-align: baseline; background-color: rgb(255, 255, 255);">Revolut also offers monthly subscription plans with higher thresholds for no fees, as well as instant access to crypto-currencies, cash back, travel and concierge service.</p><p style="border: 0px; color: rgb(64, 64, 64); font-variant-numeric: inherit; font-variant-east-asian: inherit; font-stretch: inherit; font-family: Helmet, Freesans, Helvetica, Arial, sans-serif; font-size: 1rem; line-height: 1.375; margin: 18px 0px 0px; padding: 0px; vertical-align: baseline; background-color: rgb(255, 255, 255);">Revolut\'s services are currently only available in Europe, but the firm has plans to expand into North America, Australia, Singapore and Hong Kong soon.</p></li></ul>',
        self::KEY_CATEGORY_NAME  => 'Banking',
    ];

    const ALL_CATEGORIES = [
        self::NOTE_FINANCES_POLAND_ROCK_FESTIVAL_2019_SUMMARY,
        self::NOTE_FINANCES_WORKING_ABROAD_RESUPPLY,
        self::CATEGORY_SERVICES_PHONE_ACCOUNT_DATA,
        self::CATEGORY_APPLICATIONS_LICENCE_KEY,
        self::CATEGORY_SERVICES_BANKING_REVOLUT,
        self::NOTE_GOALS_CATEGORY_B_MOTORBIKES,
        self::NOTE_DIET_DAILY_CONSUMPTION,
        self::CATEGORY_SERVICES_FESTNETZ,
        self::NOTE_CAR_SPARE_MATERIALS,
        self::CATEGORY_SERVICES_ADAC,
        self::NOTE_GOALS_TATTOO,
    ];

}