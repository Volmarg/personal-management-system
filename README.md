<p align="center">
<img src="https://github.com/Volmarg/personal-management-system/blob/master-laptop/public/assets/images/logo/smaller.png?raw=true" width="150px;" />
</p>

<h1 align="center"> Personal Management System</h1>
<p align="center"><i>Your central point for managing every personall thing that You need  <br> (if current modules allow to do that - that is).</i></p>

<h3>Description</h3>
<hr>
<p align="justify">
    I'ts easier to understand this web application when You think about CMS (Wordpress) or CRM (SugarCRM), logic behind this system is very similar to those two. My system has less possibilities than thoes above, but It just do what I want it to do. Besides writing extensions is not so hard, depending on logic behind. Anyone with proper development knowledge can pretty much write his/her own extensions for personal needs.
</p>
<p align="justify">
    As I'm working on it there will be some fixes, and new modules in future when I reach the point when I got all I need.
</p>

<h3>Reasoning/Purpose</h3>
<hr>

<p align="justify">
    I decided to create my own system, because first of all playing around with tones of plugins for wordpress and writing customizations to some existing CRMs would take me as much time as writing my own system. Besides by knowing the logic from core it's easier to write for me extensions, add additional modules, whichever i just need.
</p>

<p align="justify">
    Second of all, there is no system like that, and i didn't wanted to end up integrating  docker based cloud with cms. Furthermore i just need application like that to keep organized and I'm really tired of having some very personal data on OneDrive, other on Google cloud. Some notes here,some notes there. The end goal is having an application running on terminal or raspbery 24/7 plugged into home network, without acces to internet.
</p>

<h3>Support</h3>
<hr>

<p align="justify">
    I cannot guarantee any support. I've got a job, personall things etc, I'm just sharing my code/my application as MIT. However feel totally free to ask about something, write issues etc. As mentioned I'am using and I will use this application from now on daily so there might be some changes even good for me.
</p>

<h3>Available options and modules</h3>
<hr>

<ol>
    <li><b style="display:inline">🎯 Goals</b> - <span align="justify"><i>Keep a track of Your personal goals. You can use todolist to keep track of Your goals progress or use payments submodule to keep an eye of the money amount that You want to collect for something.</i>
        </span></li><br/>
        <li><b>📖 Notes</b> <span align="justify"> - <i>Add any personal note to desired category. You can keep here any small information that You need, it can be either quick note from phone call, bunch of information collected all around different pages or some links to things that You want to check somewhere later in future.</i></span></li><br/>
        <li><b>📞 Contacts</b> - <span align="justify">You ever feel like You got dozens of phone contacts, emails etc. that You would like to get rid of, or make some safety backup in case You loose Your phone? With this simple module You can organize Your personal contacts.
            </span></li><br/>
            <li><b>🔑 Passwords</b> - <span align="justify"> <i>We all get to certain point when there are just way to many passwords to handle all around. Yeah we can keep them on email, pendrive, have special patterns in our heads, but lets face it sometimes it's just to many. With the Passwords module You can keep Your passwords encoded in Your database, while on the frontend there is a copy button that will fetch You back the original password.
        </i></span></li><br/>
        <li><b>🏆 Achievements</b> - <span><i>Want to keep a track on anything cool You did - put in this module!</i></span></li><br/>
        <li><b>🚙 Car</b> - <span align="justify"><i>This module purpose is to keep track of any recurring things You got to do with Your car like  for example Oil changes, payments etc. While recurring type is just for information, date type is additionaly colored and used on dashboard as reminder.</i></span></li><br/>
        <li><b>🌴 Travels</b> - <span align="justify"> <i>Having some ideas of places to visit but without any specific plans to it? Add it to this Module with google map link and some image so You can come back to it at any moment. May the image remind You why You were interested in this particular place.</i></span></li><br/>
        <li><b>💸 Payments</b> - <span align="justify"> <i> Don't know how much money You spend on food, travels, domestic shoppings? Simply from now on  add every shopping details to the list and let it do all the calculations, alongside with summary for given month. </i></span>
        <p align="justify"><i>On the other hand if You would like to keep an eye of prices for particular producs You can add information about them in Prducts Prices submodule (Which I'm personally using while being in other country - on the moment when I'm writing this).</i></p></li><br/>
        <li><b>🛒 Shopping</b> - <span align="justify"><i>You got plans to buy something in future? Add it to the list, and then just check it out,  maybe You will be able to buy this particular thing just now.</i></span></li><br/>
    <li><b>💻 Job</b> - <span align="justify"><i>The Afterhours submodule is a nice way to keep an eye of all the afterhours You've made in work. With this You can also separate specified minutes/hours for certain goal. For example You need 24h for trip and 4h to do something. Just add some time to pre existing goal, create new one or leave it blank (will got to generall purpose pool).</i></span> </li><br/>
</ol>

<h2>Preview</h2>

<p align="center">Fully accessible demo can be found <a href="#"><b>⇛Here⇚</b></a>.</p>

<img src="https://github.com/Volmarg/personal-management-system/blob/master-laptop/github/preview2.png?raw=true">

<hr>

<img src="https://github.com/Volmarg/personal-management-system/blob/master-laptop/github/preview3.png?raw=true">
</div>
<h2>Tech</h2>
<p style="text-align:justify;">
    Personal Managemtn System is a web application which can be ran either in Windows and Linux enviroment. Everything is by default tested on Ubuntu 18.x.
</p>

<h3>How to install and configure</h3>
<hr>
<h4>1. Linux</h4>
<hr>
<h5>1a. Requirements</h5>
<ul>
<li>Php 7.2x</li>
<li>MySQL</li>
<li>You can just use LAMP server</li>
<li>Composer</li>
<li>NodeJs</li>
<li>Sudo</li>
</ul>

<h5>1b.Installation</h5>
<p>
<b>0.</b> I assume that You already got apache/php etc.
<br/><br/>	
<b>1.</b> Download or clone repository
<br/><br/>	
<b>2.</b> Unzip/put entire project into Your base html folder (usually /var/www/html/YourFolder/) - beware that it's better to copy it via terminal as files like .env might not be copied via gui
<br/><br/>	
<b>3.</b> Now open terminal
<br/><br/>	
<b>4.</b> Scroll to section <i>3a</i> (below)
<br/><br/>	
</p>

</ul>
<hr>
<h4>2. Windows</h4>
<hr>
<h5>2a. Requirements</h5>
<ul>
<li>Php 7.2x</li>
<li>MySQL</li>
<li>You can just use Xampp or Wamp</li>
<li>Composer</li>
<li>NodeJs</li>
</ul>

<hr>

<h5>2b.Installation</h5>
<p>
<b>0.</b> I assume that You already got apache/php etc.
<br/><br/>	
<b>1.</b> Download or clone repository
<br/><br/>	
<b>2.</b> Unzip/put entire project into Your base html folder which depends on aplication You use. Check corresponding manuals where projects folders are for Xampp or Wampp.
<br/><br/>	
<b>3.</b> Now open terminal - <b>I'm using git bash terminal</b>
<br/><br/>	
<b>4.</b> Scroll to section <i>3a</i> (below)
<br/><br/>	
</p>

<hr>

<h4>3. Common for both systems</h4>
<p>
<b>1. </b>Technical knowledge<small> (or someone with it) </small>
</p>

<hr>
<h5>3a. Configuring and installing </h5>

<p>
<b>1.</b> Run composer install (around 100mb to download)
<br/><br/>
<b>2.</b> Run npm <b>(todo)</b> ... (around 300mb to download)
<br/><br/>	
<b>3.</b> Create mysql databse however You like to (CLI/adminer/phpmyadmin - Your choice)
<br/><br/>	
<b>4.</b> Inside root of project folder You will find <b><i>.env</i></b> file - open it and add Your database connection as described in: https://symfony.com/doc/current/doctrine.html (section: Configuring the Database)
<br/><br/>	
<b>5.</b> In my case it looks like this:
<br/><br/>
	
```DATABASE_URL=mysql://user:password@127.0.0.1:3306/pms```
<br/><br/>
<b>6.</b> In <i><b>.env</b></i> set application enviroment to Production
<br/><br/>
```APP_ENV=dev``` to ```APP_ENV=prod```
<br/><br/>
<b>7.</b> In <i><b>.env</b></i> disable debugging
<br/><br/>
`APP_DEBUG=0`
<br/><br/>
<b>8.</b> In terminal go to Your project root folder (example: cd/var/www/html/YourFolder)
<br/><br/>
<b>9.</b> Now run this commands one ofter another
<br/><br/>
`bin/console cache:clear`
<br/><br/>
`bin/console cache:warmup`
<br/><br/>	
`bin/console doctrine:migrations:diff`
<br/><br/>
`bin/console doctrine:migrations:migrate (accept)`
<br/><br/>	
`bin/console fos:user:create --super-admin`
<br/><br/>	
<b>10.</b> Now run symfony server as described: https://symfony.com/doc/current/setup/built_in_web_server.html
Just run this command from root folder of project:
<br/><br/>
`bin/console server:run 0.0.0.0:8001` 
<br/><br/>
(or other port if this one is used)
<br/><br/>
<b>11.</b> You can now access Your project in browser: http://127.0.0.1:8001 (or other port You added)
<br/><br/>	
<b>12.</b> Access data (change it in user panel!)
<br/><br/>
<i>Login: admin </i>
<br/><br/>	
<i>Password: password </i>
<br/><br/>	
</p>

<hr>

<h3>Future development plans</h3>
<hr>

<ul>
<li><b>Very basic documentation</b> - moving setup to it (readme.md doc is hard to style)</li>
<li><b>Images module</b> - (with preview)
	<ul>
    	<li><b>Gallery</b> - for personal photos</li>
        <li><b>Scans</b> - for scanned documents</li>
        <li><b>Pictures</b> - for any other images</li>
        <li><b>Upload</b></li>
        <li><b>Settings</b> - for folders naming etc</li>
    </ul>
</li>
<li><b>Files module</b> - file explorer (no preview)
	<ul>
    	<li><b>Documents</b></li>
        <li><b>Upload</b></li>
        <li><b>Settings</b> - for folders naming etc</li>
    </ul>
</li>
<li><b>Job search</b> - (VERY future development plan, not sure if will make it at all)
	<ul>
    	<li>I have totally different project on laravel for searching job on portals it can be turned into module but requires bigger amount of time, so that's just an idea. I don't really need it as module - so far far on end.</li>
	</ul>
</li>
</ul>

<h4>Used languages</h4>
<hr>
<ul>
<li>Php 7.2.x</li>
<li>JS</li>
<li>JQ</li>
<li>Symfony 4.2.x</li>
<li>MySQL</li>
<li>Css</li>
<li>Scss</li>
<li>Node</li>
<li>Bootstrap</li>
<li>Webpack</li>
<li><small>And maybe some others which I just don't remember</small></li>
</ul>

<h4>Used packages</h4>
<ul>
<li><a href="https://github.com/krzysiekpiasecki/Symfonator">Symfonator</a> <small>(Ui)</small></li>
<li><a href="https://github.com/mogilvie/EncryptBundle">EncryptBundle</a> <small>(Core for passwords module)</small></li>
<li><a href="https://github.com/furcan/IconPicker">Icon Picker</a> <small>(Icon picker for Notes module)</small></li>
<li><a href="https://github.com/furcan/IconPicker">Bootbox</a> (<small>Additional safety confirmations for CRUD</small>)</li>
<li><a href="https://github.com/EastDesire/jscolor">JsColor</a> (<small>Color pickers for Notes module</small>)</li>
<li>... bootstrap, TinyMce, DataTables and many others</li>
<li>... I don't even know some of them which come prebuild in Symfonator</li>
</ul>

<h5>Special thanks to</h5>
<ul>
<li></li>
<li></li>
<li></li>
</ul>
<hr>