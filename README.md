<p align="center">
<img src="https://github.com/Volmarg/personal-management-system/blob/beta1.0/public/assets/images/logo/smaller.png?raw=true" width="150px;" />
</p>
<p align="center">
<img src="https://github.com/Volmarg/personal-management-system/blob/beta1.0/github/bar.jpg?raw=true" />
</p>

<h1 align="center"> Personal Management System</h1>
<p align="center"><i>Your central point for managing personal data <br> (if current modules allow to do that).</i></p>

<h3>Description</h3>
<hr>
<p align="justify">
	It's easier to understand this web application when you think about a CMS (WordPress) or CRM (SugarCRM); the logic behind this system is very similar to those two. My PMS may offer fewer possibilities than those systems above, but it just does what I want it to do. Additionally, writing extensions is not too hard, depending on the logic required. Anyone with development knowledge can pretty much write their own extensions for personal needs.
</p>

<h3>Reasoning/Purpose</h3>
<hr>

<p align="justify">
    I decided to create my own system, because playing around with tons of plugins for WordPress and writing customizations to some existing CRMs would take me as much time as writing my own system and by knowing the logic from its core it's easier for me to write extensions and add additional modules - whatever I need.
</p>

<p align="justify">
    Secondly, there is no system like that, and I didn't wanted to end up integrating a docker based cloud with CMS. Furthermore I just need an application like that to keep organized and I'm tired of having some very personal data on OneDrive, other data on Google cloud, some notes here, and some notes there. The end goal is to have an application running on terminal or raspberry 24/7 plugged into my home network, without access to internet.
</p>

<h3>Available options and modules</h3>
<hr>

<ol>
    <li><b style="display:inline">🎯 Goals</b> - <span align="justify"><i>Keep a track of your personal goals. You can use todolist to keep track of your goals progress or use payments submodule to keep an eye of the money amount that you want to collect for something.</i>
        </span></li><br/>
        <li><b>📖 Notes</b> <span align="justify"> - <i>Add any personal note to desired category. Here, you can keep any small information that you need; it can be either quick notes from phone call, bunch of information collected all around different pages or some links to things that you want to check somewhere later in future.</i></span></li><br/>
        <li><b>📞 Contacts</b> - <span align="justify">You ever feel like You got dozens of phone contacts, emails etc. that you would like to get rid of, or make some safety backup in case you loose your phone? With this simple module you can organize your personal contacts.
            </span></li><br/>
        <li><b>🔑 Passwords</b> - <span align="justify"> <i>We all get to certain point when there are just way too many passwords to handle all around. Yeah we can keep them on email, pendrive, have special patterns in our heads, but lets face it sometimes it's just too many. With the Passwords module You can keep Your passwords encrypted in Your database, while on the frontend there is a copy button that will fetch You back the original password.
        </i></span></li><br/>
        <li><b>🏆 Achievements</b> - <span><i>Want to keep a track on anything cool you did - put in this module!</i></span></li><br/>
        <li><b>🚙 Car</b> - <span align="justify"><i>This module purpose is to keep track of any recurring things you got to do with your car like  for example oil changes, payments etc. While recurring type is just for information, date type is additionally colored and used on dashboard as reminder.</i></span></li><br/>
        <li><b>🌴 Travels</b> - <span align="justify"> <i>Having some ideas of places to visit but without any specific plans to it? Add it to this Module with google map link and some image so you can come back to it at any moment. May the image remind you why you were interested in this particular place.</i></span></li><br/>
        <li><b>💸 Payments</b> - <span align="justify"> <i> Don't know how much money You spend on food, travels, domestic shopping? Simply from now on  add every shopping details to the list and let it do all the calculations, alongside with summary for given month. </i></span>
        <p align="justify"><i>On the other hand if you would like to keep an eye of prices for particular products you can add information about them in Products Prices submodule (which I'm personally using while being in other country - on the moment when I'm writing this).</i></p></li><br/>
        <li><b>🛒 Shopping</b> - <span align="justify"><i>You got plans to buy something in future? Add it to the list, and then just check it out,  maybe You will be able to buy this particular thing just now.</i></span></li><br/>
    <li><b>💻 Job</b> - <span align="justify"><i>The Afterhours submodule is a nice way to keep an eye of all the afterhours you've made in work. With this you can also separate specified minutes/hours for certain goal. For example you need 24h for trip and 4h to do something. Just add some time to pre existing goal, create new one or leave it blank (will go to general purpose pool).</i></span> </li><br/>
    <li><b>📷 Images</b> - <span align="justify"><i>This module allows You to organize Your photos/scans/downloaded pics in form of masonry galleries. Clicking on image miniature will call lightbox gallery with possibility to rename, remove or download image. You can create as many galleries (folders) as You want.</i></span> </li><br/>	
    <li><b>📁 Files</b> - <span align="justify"><i>Files uploaded for this module are visible in form of DataTable where You can see simple information about the file - that is: extension, filetype icon (if there is one defined for given type), file size. Files can be renamed, downloaded and removed from the gui.</i></span> </li><br/>		
</ol>

<h2>Preview</h2>

<p align="center">Fully accessible demo can be found <a href="http://185.204.216.136:8000"><b>⇛Here⇚</b></a>.</p>
<ul>
	<li><b>Login:</b> admin</li>
	<li><b>Password:</b> admin</li>
</ul>
<p><i><b>Info!</b> All data on demo instance is removed and <b>generated</b> each day. If the demo is not working then either someone did his best to enter some untested invalid data, or server is simply down.</i></p>
	
<img src="https://github.com/Volmarg/personal-management-system/blob/beta1.0/github/preview2.png?raw=true">

<hr>

<img src="https://github.com/Volmarg/personal-management-system/blob/beta1.0/github/preview3.png?raw=true">
</div>

<hr>

<h2>How to install/documentation</h2>
You can find all the informations <a href="https://volmarg.github.io"><b>⇛Here⇚</b>.

<h2>Future development plans</h2>

<h3>Modules/Extensions</h3>

<ul>
<li><b>💸 Money owned</b> - extension for <b>Payments</b>
	<ul>
    	<li>For saving information who owe You money and who You owe money to</li>
    </ul>
</li>
<li><b>💸 Bills</b>  (<i>Temporary name</i>) - extension for <b>Payments</b>
	<ul>
	<li>For saving information about money spent on certain things (<i>I want to separate monthly payments from things like money spent on holiday etc.</i>)</li>
    </ul>
</li>	
<li><b>📁 Files module</b> - improvements
	<ul>
    	<li>Bugfixes</li>
	<li>Handling server responses</li>	
	<li>Adding nested menu like in <b>Notes</b> module</li>
	<li>Moving files to other modules/folders from view of given module</li>
    </ul>
</li>
<li><b>🔎 Job search</b> - (VERY future development plan, not sure if will make it at all)
	<ul>
    	<li>I have totally different project on laravel for searching job on portals it can be turned into module but requires bigger amount of time, so that's just an idea. I don't really need it as module - so far far on end.</li>
	</ul>
</li>
</ul>

<h3>Improvements</h3>
<p>
	<i>This things are planned for way later - first of all i need additonal working modules.</i>
</p>	
<ul>
	<li>Better page content reload with ajax</li>
	<li>Better CRUD for tables - preventing removing <i>td</i> subelements</li>
	<li>Dashboard settings - which modules widgets should be displayed</li>
	<li>Recurring payments for </b>Payments</b> module</li>
	<li>Adding images for <b>Contacts</b> </li>
	<li>Auto deployment - creating full working instance with one bash command </li>	
</ul>
<hr>

<h2>Support</h2>

<p align="justify">
    I cannot guarantee support. I've got a job, personal things etc, I'm just sharing my code/my application as MIT. However feel totally free to ask about something, write issues etc. As mentioned I'm using and I will use this application from now on daily so there might be some changes even good for me.
</p>

<p align="justify">
    As I'm working on it there will be some fixes, and new modules in future when I reach the point when I got all I need. So after reaching my goal I could handle some support requests if anyone will be interested.
</p>

<h3>Browsers Support</h3>
<ul>
	<li>Chrome</li>
</ul>

<p>
	I mean for real. Just <b>Chrome</b>. I know about bug with copying password in Firefox for example. But I just do everything in Chrome so I'm not really planning to check that on other browsers. 
</p>

<hr>

<h2>Tech</h2>
<p style="text-align:justify;">
    Personal Management System is a web application which can be ran either in Windows and Linux enviroment. Everything is by default tested on Ubuntu 18.x.
</p>

<h3>Used languages/frameworks/solutions</h3>

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

<h3>Used packages</h3>
<ul>
<li><a href="https://github.com/krzysiekpiasecki/Symfonator">Symfonator</a> <small>(Ui)</small></li>
<li><a href="https://github.com/mogilvie/EncryptBundle">EncryptBundle</a> <small>(Core for passwords module)</small></li>
<li><a href="https://github.com/furcan/IconPicker">Icon Picker</a> <small>(Icon picker for Notes module)</small></li>
<li><a href="https://github.com/makeusabrew/bootbox">Bootbox</a> (<small>Additional safety confirmations for CRUD</small>)
</li>
<li><a href="https://github.com/mbezhanov/faker-provider-collection">Faker Provider Collection</a> <small>(<a href="https://github.com/fzaninotto/faker">Faker</a> extension)</small></li>
<li><a href="https://github.com/EastDesire/jscolor">JsColor</a> (<small>Color pickers for Notes module</small>)</li>
<li>... bootstrap, TinyMce, DataTables and many others</li>
<li>... I don't even know some of them which come prebuild in Symfonator</li>
</ul>

<h2>Special thanks to</h2>
<ul>
	<li><a href="https://github.com/mogilvie">Mark Ogilvie</a> - for fixing EncryptBundle bugs for Symfony 4</li>
	<li><a href="https://github.com/furcan">Furcan</a> - for adding my idea of contenteditable support for Icon Picker</li>
</ul>
<hr>
