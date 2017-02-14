---------------------------------------------
Prerequisites
---------------------------------------------
1. Install Lemur 4.12 on your system. You can see a Lemur installation guide for Linux in http://www.lemurproject.org/lemur/install.php. 
2. Run install.sh before executing run.sh. 

---------------------------------------------
How to run
---------------------------------------------
1. Put the address of the profiles and publications in simple text format and the association file between experts and publications in the first lines of run.sh file. You should also edit the numProfile and numQueries in this file. Before executing run.sh, you should put your query file in this address: CommunityBasedEF/conf/queryConf/query.ldf. Now, you can execute the run.sh file. Your output will be in the output folder with the name "TopFinalExperts.out". It will show you the top 3 experts for each query.
