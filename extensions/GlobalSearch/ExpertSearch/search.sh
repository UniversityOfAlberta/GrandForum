#!/bin/bash
#Enter Path to Your data!
profiles="expert/profiles"
assocFile="expert/experts.txt"
numProfile=`ls -1 $profiles | wc -l`
hash=`echo -n "$1\`date +%s%N\`" | md5sum | awk '{print $1}'`
hash=`echo -n "$1" | md5sum | awk '{print $1}'`
numQueries="1"

#Runnig Community-based EF
mkdir -p "Index/indexFile/$hash"
mkdir -p "Index/conf/$hash"
mkdir -p "CommunityBasedEF/conf/queryConf/$hash"
mkdir -p "CommunityBasedEF/conf/queryConf/kl/output/$hash"
mkdir -p "CommunityBasedEF/output/kl/$hash"
queryFile="CommunityBasedEF/conf/queryConf/$hash/query_profiles.ldf"
klOutFile="CommunityBasedEF/output/kl/$hash/klOutfile.txt"

echo "<DOC 1>
$1
</DOC>" > "CommunityBasedEF/conf/queryConf/$hash/query.ldf"

./CommunityBasedEFPaper $queryFile "Index/indexFile/profiles_index.key" "" $klOutFile "Clustering/output/papers-clusters.csv" $assocFile "Index/indexFile/publications_clusters_index.key" "all" $numProfile "0.9" "0.5" "/dev/null" "Constrained" "10" "/dev/stdout" "0.6" "0.6" "/dev/null" $numQueries $hash

rm -fr "Index/indexFile/$hash"
rm -fr "Index/conf/$hash"
rm -fr "CommunityBasedEF/conf/queryConf/$hash"
rm -fr "CommunityBasedEF/conf/queryConf/kl/output/$hash"
rm -fr "CommunityBasedEF/output/kl/$hash"
