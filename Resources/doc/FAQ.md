1. What are differences between API and xAPI?

- xAPI is implementation of Experiment API.
- API is for external device to manage site resources (Analytics, Report, Site, …)

2. How to test app with Goft & Tetris?

- Download: http://tincanapi.com/download-prototypes/
- To configure them to report statements to an LRS, you need to edit the config.js (rename it from config.js.template to just config.js in your TinCan_Prototypes folder).
- You’ll want to change the config file to contain the correct endpoint for the LRS and the actor name and email address.
- To report to the LRS, use follow configurations:
Config.endpoint = "http://ll-quan.aduro.go1.com.vn/data/xAPI/";
Config.authUser = "149d017d7b2ec3d5b63ff320a469542bb7ad3459";
Config.authPassword = "8631bd2b03c1425a55e0771c6524716b86097981";
Config.actor = { "mbox":["mailto:name@domain.com"], "name":["First Last"] };

- How to get your configurations? 
	+ Go to your LRS. 
	+ Login as your first user.
	+ Go to LRS List page (http://lrs.example.com/lrs).
	+ Go to any LRS item & xapi statements page of that lrs (http://lrs.example.com/lrs/536b02d4c01f1325618b4567/endpoint).

- Run the prototype (index.html) and go through the activities. You can see statements in the statement viewer, report sample in the index file or by logging to your LRS and looking statements page.

