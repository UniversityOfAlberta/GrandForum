#!/bin/bash
#Enter Path to Your data!
profiles="expert/profiles"
publications="expert/publications"
assocFile="expert/experts.txt"
numProfile=`ls -1 $profiles | wc -l`
numQueries="1"

#Runnig Community-based EF
mkdir "Output/"
queryFile="CommunityBasedEF/conf/query_profiles.ldf"
klOutFile="CommunityBasedEF/output/kl/klOutfile.txt"
klConfigFile="CommunityBasedEF/conf/KLconfile"

echo "<DOC 1>
$1
</DOC>" > CommunityBasedEF/conf/queryConf/query.ldf

./CommunityBasedEFPaper $queryFile "Index/indexFile/profiles_index.key" $klConfigFile $klOutFile "Clustering/output/papers-clusters.csv" $assocFile "Index/indexFile/publications_clusters_index.key" "all" $numProfile "0.9" "0.5" "Output/TopExperts.out" "Constrained" "10" "/dev/stdout" "0.6" "0.6" "Output/TopFinalExperts.out" $numQueries
