CakePHP sumCache
=================

Updates the sumCache fields of belongsTo associations after a save or delete operation
An extension of CakePHP's built-in counterCache mechanism
Based on article: http://paulherron.net/articles/view/counting_users_votes_with_a_cakephp_sum_cache

Refinements and improvements by Iain Mullan

NB. This code has only been tested with a MySQL datasource. Due to the use of the SUM() function (ie. DBMS-specific SQL), it's reliability with other types of datasource is not guaranteed.
