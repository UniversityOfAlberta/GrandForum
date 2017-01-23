#!/bin/bash
#Enter Path to Your data!
profiles="expert/profiles"
publications="expert/publications"
assocFile="expert/experts.txt"
numProfile=`ls -1 $profiles | wc -l`
numQueries="1"
#Topic Modeling
cd TopicModeling
mkdir output
bin/mallet import-dir --input ../$publications --output output/papers-input.mallet --keep-sequence --remove-stopwords
bin/mallet train-topics --input output/papers-input.mallet --num-topics 25 --output-doc-topics output/papers-doc-topics.csv
cd ..
#Convert Topic File to Weka Format
java -jar ConvertMalletToWeka.jar "TopicModeling/output/papers-doc-topics.csv" "Clustering/input/papers-doc-topics-weka.csv" "mapExperts.txt" "25"

#Loading CSV File in Weka

cd Clustering
mkdir input
mkdir output
mkdir trecData

java -cp weka.jar weka.core.converters.CSVLoader input/papers-doc-topics-weka.csv > input/papers-doc-topics-weka.arff

#Clustring
java -cp weka.jar weka.filters.unsupervised.attribute.AddCluster -W "weka.clusterers.SimpleKMeans -N 10 -I 500 -S 10" -i input/papers-doc-topics-weka.arff -o output/papers-clusters-out.arff

cd ..
#Convert Weka Clustering Output to Our method input format
java -jar ConvertWekaToOurMethod.jar "Clustering/output/papers-clusters-out.arff" "Clustering/output/papers-clusters.csv" "mapExperts.txt"

#Create trectext files and datafile to index
rm Index/conf/buildfile_publications.txt
rm Index/conf/buildfile_profiles.txt
mkdir Index/indexFile
mkdir "$publications"_"trectext/"
mkdir "$profiles"_"trectext/"
java -jar writeTrecTexFile.jar "$publications/" "$publications"_"trectext/" "Index/conf/buildfile_publications.txt"
java -jar writeTrecTexFile.jar "$profiles/" "$profiles"_"trectext/" "Index/conf/buildfile_profiles.txt"

#Index publications and profiles
rm Index/conf/BuildConfFile_publications.txt
rm Index/conf/BuildConfFile_profiles.txt
rm -r Index/indexFile/publications_index.*
rm -r Index/indexFile/profiles_index.*

echo "<parameters>\n<index>Index/indexFile/publications_index</index>\n<indexType>key</indexType>\n<dataFiles>Index/conf/buildfile_publications.txt</dataFiles>\n<docFormat>trec</docFormat>\n<stopwords>Index/conf/stopwords.list</stopwords>\n<memory>2G</memory>\n</parameters>" > "Index/conf/BuildConfFile_publications.txt"
BuildIndex Index/conf/BuildConfFile_publications.txt

echo "<parameters>\n<index>Index/indexFile/profiles_index</index>\n<indexType>key</indexType>\n<dataFiles>Index/conf/buildfile_profiles.txt</dataFiles>\n<docFormat>trec</docFormat>\n<stopwords>Index/conf/stopwords.list</stopwords>\n<memory>2G</memory>\n</parameters>" > "Index/conf/BuildConfFile_profiles.txt"
BuildIndex Index/conf/BuildConfFile_profiles.txt

#Create Cluster file
./CreateDataFile "Clustering/output/papers-clusters.csv" "Clustering/trecData/papers-clusters.trectext" "Index/indexFile/publications_index.key"

#Index Cluster file
rm Index/conf/BuildConfFile_publications_clusters.txt
rm Index/conf/buildfile_publications_clusters.txt
rm -r Index/indexFile/publications_clusters_index.*

echo "Clustering/trecData/papers-clusters.trectext" > "Index/conf/buildfile_publications_clusters.txt"

echo "<parameters>\n<index>Index/indexFile/publications_clusters_index</index>\n<indexType>key</indexType>\n<dataFiles>Index/conf/buildfile_publications_clusters.txt</dataFiles>\n<docFormat>trec</docFormat>\n<stopwords>Index/conf/stopwords.list</stopwords>\n<memory>2G</memory>\n</parameters>" > "Index/conf/BuildConfFile_publications_clusters.txt"
BuildIndex Index/conf/BuildConfFile_publications_clusters.txt

#Runnig Community-based EF
queryFile="CommunityBasedEF/conf/query_profiles.ldf"
klOutFile="CommunityBasedEF/output/kl/klOutfile.txt"
klConfigFile="CommunityBasedEF/conf/KLconfile"

GenerateSmoothSupport "CommunityBasedEF/conf/supp_param"
rm -f $klOutFile
rm -f $queryFile
