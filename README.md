# Mijengo Online Portal
Mijengo is an online portal where Small businesses/Suppliers can register their company and get paid for supplying building material and equipment to large construction projects and get paid quickly.

Mijengo V3
-------------------------------------------

Folder structures
****************
-app
---Helpers
---Http
----Middleware
----Controllers
-----Admin
-----Api
-----Auth
-----Contractors
-----Vendors
----Mail
----Providers
-config
-database
--migrations
-resources
--view
---admin
---auth
---emails
---errors
---layouts
-routes
--admin
--web
--vendor

Functionalities
****************

-Auth
========================
--login
--otp via email- done
--reset password- pending

ADMIN
----------------------------------------------------------------------------------
Dashboard
========================
-stats
--Invoices
--Projects
--Businesses
--Inventory


-Project
========================
--View - pending
--Edit- done
--Publish/Draft- pending
--Sites
---add- done
---edit- done
---delete- done
--Material Required
---add- done
---edit- done
---delete- done
--Equipment Required
---add- done
---edit- done
---delete- done

-Businesses
========================
-Contractor business
--add- done
--edit- done
--delete- done
--Assign staff to sites
--Add staffs to sites

-Vendor/Supplier Business
--Add- pending
--Edit- pending
--Delete- pending
--Upload Doc- pending
--Deactivate business- pending
--Assign staff to equiments/materials- pending
--Add staffs to equiments/materials- pending
-Inventory
========================
-Equipment
--View -pending
--add- pending
--edit- pending
--delete- pending
--approve/reject- pending
-Material
--View -pending
--add- pending
--edit- pending
--delete- pending
--approve/reject- pending
-Billing
========================
-View
-Edit

-System User
========================
--add - done
--edit- done
--delete- done
--assign roles- done
--create roles- pending
--edit roles- pending
--update roles- pending
--delete roles- pending

- User profile/setting
  ========================
  --change theme
  --Update profile

VENDORS
----------------------------------------------------------------------------------
Dashboard
==========
-stat
--Inventory
--projects
--Invoices

Onboarding
==========
-Signup
-Email Verification
-Complete profile page
-Upload doc -business kyc
-Updated uploaded files

Tasks
=====================
-View project task assign to them

Inventory
===========
-Equipment
--View -pending
--add- pending
--edit- pending
--delete- pending
--change the equipment status- out of service/maitainance/working condition
-Material
--View -pending
--add- pending
--edit- pending
--delete- pending
--change the material status- available/not available

User
==============
-Add
-edit
-delete
-Assign equipment to manage
-Assing Material to manage

-Billing
========================
-View Jobs -completed/issues
-View Invoices -pending/paid -raise an issue on invoices



CONTRACTOR
----------------------------------------------------------------------------------

Dashboard
========================
-stats
--Invoices
--Projects
--Businesses
--Inventory

Projects
========================
-View my projects
---each task and vendor assign to it and equipment details
-Assign/Detach Staff to projects->sites->task

Billing
===========================
-Activities done/issues under the Task
-Invoice pending/paid

Users
===========================
-Add
-edit
-delete
-Assign/Detach Staff to projects->sites->task

- User profile/setting
  ========================
  --change theme
  --Update profile 


