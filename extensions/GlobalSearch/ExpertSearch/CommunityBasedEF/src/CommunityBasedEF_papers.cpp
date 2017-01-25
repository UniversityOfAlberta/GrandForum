#include <stdio.h>
#include <stdlib.h>
#include <time.h>
#include <sys/types.h>
#include <dirent.h>
#include <cmath>
#include <map>
#include <vector>
#include <list>
#include <algorithm>
#include <fstream>
#include <iostream>
#include <sstream>
#include <string>
#include <string.h>
#include <boost/algorithm/string.hpp>


#include "IndexManager.hpp"
#include "BasicDocStream.hpp"
#include "ResultFile.hpp"
#include "RetParamManager.hpp"

using namespace lemur::api;
using namespace boost::algorithm;
using namespace std;

void readTopics(map<int , vector<double> >& Topics, string path)
{
	ifstream fout(path.c_str());

	string line;
	int id = 1;
//reading attribute line
	getline(fout,line);
	while(getline(fout, line))
	{
		//cout<<id<<":"<<line<<endl;
		istringstream iss(line);
		string temp;
		while(getline(iss,temp,','))
		{
			Topics[id].push_back(atof(temp.c_str()));
		}
		id++;
	}
	fout.close();
}
void AvgTwoVectors(vector<double>& meanClusterVectors_i,vector<double> pTopics_i, int l)
{
	for(int j=0; j<pTopics_i.size(); j++)
	{
	//	cout<<"mc : "<<meanClusterVectors_i[j]<<"ptopics[j] "<<pTopics_i[j]<<endl;
		meanClusterVectors_i[j] += (double) pTopics_i[j]/l;
	}
}
void FindMeandOfClusters(map<int , vector<double> >& meanClusterVectors, map<int , vector<double> > paperTopics, map<int , map<double, list<int> > > clusters,int numoftopic)
{
	for(map<int , map<double, list<int> > >::iterator cit = clusters.begin(); cit != clusters.end(); cit++)
	{
		//cout<<"cluster is : "<<cit->first<<endl;
		//initilize meanClusterVectors
		for(int j=0; j<numoftopic; j++)
		{
			meanClusterVectors[cit->first].push_back(0);
		}
		for(map<double , list<int> >::iterator sit = cit->second.begin(); sit != cit->second.end(); sit++)
		{
			for(list<int>::iterator pit = sit->second.begin(); pit != sit->second.end(); pit++)
			{
				//cout<<" papers is : "<<*pit<<endl;
				AvgTwoVectors(meanClusterVectors[cit->first],paperTopics[*pit],sit->second.size());
			}
			
		}
	}
}
double distance(vector<double> q , vector<double> c)
{
	double sum2 = 0;
	for(int i = 0; i<q.size(); i++)
	{
		sum2 += pow(q[i]-c[i],2);
	}
//	return sum2;
	return sqrt(sum2);
}
void FindClusterOfEachQuery(list<int>& qpapers, map<int , int>& queryClusters,string cIndexPath, string hash)
{
	cout<<"Cluster index path is : "<<cIndexPath<<endl;
	ifstream qin("CommunityBasedEF/conf/queryConf/" + hash + "/query.ldf");
	ofstream qout("CommunityBasedEF/conf/queryConf/" + hash + "/query.trectext");
	string line;
	string content = "";
	int qid = -1;
	while(getline(qin, line))
	{
		if(line.find("<DOC ") != string::npos)
		{
			std::size_t pos1 = line.find("<DOC");
			string sub = line.substr(pos1+4);
			sub.erase(sub.end()-1);
			cout<<"sub string is : "<<sub<<endl;
			qid = atoi(sub.c_str());
			cout<<qid<<endl;
			qpapers.push_back(qid);
			content = "";
		}
		else
		{
			if(line.find("</DOC>") == string::npos)
			{
				content +=line;
			}
			else
			{
				qout << "<DOC>"<< endl;
				qout << "<DOCNO>" <<qid<< "</DOCNO>" << endl;
				qout << "<TEXT>" << endl;
				qout<<content<<endl;
				qout<< "</TEXT>" <<endl;
				qout<< "</DOC>" <<endl;
			}
		}
	}
	qout.close();
	qin.close();
	//Index queries
	ofstream dFile("Index/conf/" + hash + "/buildfile_queries.txt");
	ofstream buildFile("Index/conf/" + hash + "/BuildConfFile_queries.txt");
	dFile<<"CommunityBasedEF/conf/queryConf/" + hash + "/query.trectext";
	buildFile<<"<parameters>\n<index>Index/indexFile/" + hash + "/queries_index</index>\n<indexType>key</indexType>\n<dataFiles>Index/conf/" + hash + "/buildfile_queries.txt</dataFiles>\n<docFormat>trec</docFormat>\n<stopwords>Index/conf/stopwords.list</stopwords>\n<memory>2G</memory>\n</parameters>"<<endl;
	dFile.close();
	buildFile.close();

	string command = "BuildIndex Index/conf/" + hash + "/BuildConfFile_queries.txt";
    int returnval = system(command.c_str());
	//run kl to find closest cluster
	ofstream klConfFileQuery("CommunityBasedEF/conf/queryConf/" + hash + "/klConfFile.txt");
	ofstream smoothParamFile("CommunityBasedEF/conf/queryConf/" + hash + "/klSmoothConf.supp");

	smoothParamFile<<"<parameters>\n<index>"<<cIndexPath<<"</index>\n<smoothSupportFile>CommunityBasedEF/conf/queryConf/" + hash + "/klSmooth.supp</smoothSupportFile>\n</parameters>"<<endl;

	klConfFileQuery<<"<parameters>\n<retModel>2</retModel>\n<index>"<<cIndexPath<<"</index>\n<textQuery>CommunityBasedEF/conf/queryConf/" + hash + "/query.ldf</textQuery>\n<resultFile>CommunityBasedEF/conf/queryConf/kl/output/" + hash + "/klOutfile.txt</resultFile>\n<resultCount>10</resultCount>\n<resultFormat>1</resultFormat>\n<smoothSupportFile>CommunityBasedEF/conf/queryConf/" + hash + "/klSmooth.supp</smoothSupportFile>\n<smoothStrategy>0</smoothStrategy>\n<smoothMethod>0</smoothMethod>\n<discountDelta>0.5</discountDelta>\n<JelinekMercerLambda>0.5</JelinekMercerLambda>\n<DirichletPrior>13567</DirichletPrior>\n<feedbackDocCount>0</feedbackDocCount>\n<feedbackTermCount>20</feedbackTermCount>\n</parameters>"<<endl;

	smoothParamFile.close();
	klConfFileQuery.close();
	
	command = "GenerateSmoothSupport CommunityBasedEF/conf/queryConf/" + hash + "/klSmoothConf.supp";
     	returnval = system(command.c_str());
	
	command = "RetEval CommunityBasedEF/conf/queryConf/" + hash + "/klConfFile.txt";
     	returnval = system(command.c_str());
	ifstream klout;
	klout.open("CommunityBasedEF/conf/queryConf/kl/output/" + hash + "/klOutfile.txt");
	if(klout.good())
	{
		string l;
		bool isEmpty = true;
		while(getline(klout, l))
		{
			istringstream iss(l);
			int qid,cid;
			double score,rank;
			string q0,exp;
			iss >>qid>>q0>>cid>>rank>>score>>exp;	
			queryClusters.insert(std::pair<int,int>(qid,cid));
			isEmpty = false;
		
		}
		klout.close();
		if(isEmpty == true)
		{
		
			for(list<int>::iterator q = qpapers.begin(); q!=qpapers.end(); q++)
			{	
				
				queryClusters.insert(std::pair<int,int>(*q,0));
			}
		}
	}
	else
	{
		
		for(list<int>::iterator q = qpapers.begin(); q!=qpapers.end(); q++)
		{	
			queryClusters.insert(std::pair<int,int>(*q,0));
		}
	}
	
}
//	readAllAffinity(argv[2] , argv[4], PRAffinity,papers.size(),atoi(argv[6]));
void readAllAffinity(string out, map<int, map<int , double> > & PRAffinity)
{
	ifstream fout(out.c_str());
	//ofstream output(out2.c_str());
	string line;
	while(getline(fout, line))
	{
		istringstream iss(line);
		int qid,rid;
		double score,rank;
		string q0,exp;
		iss >>qid>>q0>>rid>>rank>>score>>exp;	
		PRAffinity[qid][rid] = score;
		
	}
}
void readAllAffinityRS(string out, map<int, IndexedRealVector > & PRAffinity)
{
	ifstream fout(out.c_str());
	//ofstream output(out2.c_str());
	string line;
	while(getline(fout, line))
	{
		istringstream iss(line);
		int qid,rid;
		double score,rank;
		string q0,exp;
		iss >>qid>>q0>>rid>>rank>>score>>exp;	
		PRAffinity[qid].PushValue(rid,score);
		
	}
	for(map<int, IndexedRealVector >::iterator qit = PRAffinity.begin(); qit != PRAffinity.end(); qit++)
	{
		(qit->second).LogToPosterior();
		// sort 
		(qit->second).Sort();  
	}
}
void sortedPapersBasedOnTopics(string p , map<int , list<int> >&papers, string dataset)
{
	//cout<<"paper file is :"<<p<<endl;
	map<int , list<string> > pTopics;
	if(dataset == "pubMed")
	{
		ifstream tuk(p.c_str());
		string line;
		int count = 0;
		while(getline(tuk, line))
		{
			istringstream iss(line);
			vector<string> tokens;
			string topics;
			while(getline(iss, topics , '\t'))
			{
				tokens.push_back(topics);
			}
			istringstream iss2(tokens[1]);
			string topics2;
			while(getline(iss2, topics2 , ' '))
			{
				trim(topics2);
				pTopics[atoi(tokens[0].c_str())].push_back(topics2.c_str());
			}
		}
		tuk.close();
	}
	else
	{	
		ifstream tuk(p.c_str());
		string line;
		int count = 0;
		while(getline(tuk, line))
		{
			
			size_t foundID = line.find(".");
			string qid = line.substr(0, foundID);
			getline(tuk, line);
			istringstream iss(line);
			string topics;
			while(getline(iss, topics , ' '))
			{
				trim(topics);
				if(topics != "")
					pTopics[atoi(qid.c_str())].push_back(topics);
			}
		}
		tuk.close();
	}
	
	for(map<int , list<string> >::iterator pit = pTopics.begin(); pit!= pTopics.end(); pit++)
	{
	//	cout<<pit->second.size()<<" "<<pit->first<<endl;
		papers[pit->second.size()].push_back(pit->first);
	}
}
void sortPRAffinity(map<int, map<double , list<int> > >& SortedPRAffinity, map<int, map<int , double > > PRAffinity)
{
	for(map<int , map<int , double > >::iterator pit = PRAffinity.begin(); pit != PRAffinity.end(); pit++)
	{
		for(map<int , double>::iterator rit = pit->second.begin(); rit != pit->second.end(); rit++)
		{
			SortedPRAffinity[pit->first][rit->second].push_back(rit->first);
		}
		
	}
}
double score(double v,int paper, int rid, list<int> assigned, map<int, map<int , double > > PRAffinity, map<int, IndexedRealVector > reviewersS)
{
	list<int> tempAssign;
	for(list<int>::iterator ait = assigned.begin(); ait != assigned.end(); ait++)
	{
		tempAssign.push_back(*ait);
	}
	tempAssign.push_back(rid);
	
	double novelty = 0;
	for(list<int>::iterator ait = tempAssign.begin(); ait != tempAssign.end(); ait++)
	{
		for(list<int>::iterator bit = tempAssign.begin(); bit != tempAssign.end(); bit++)
		{
			if(*ait != *bit)
			{
				for ( int i = 0; i < reviewersS[*ait].size(); i++ ) 
				{
					const double& value = reviewersS[*ait][i].val;
					int docID = reviewersS[*ait][i].ind;
					if(docID == *bit)
					{
						novelty += value;
						break;
					}
				}
			}
		}
	}
	novelty = (double) novelty/2;
	double rel = 0;
	for(list<int>::iterator ait = tempAssign.begin(); ait != tempAssign.end(); ait++)
	{
		rel += PRAffinity[paper][*ait];
	}
	
	double s = (v)*rel - (1-v)*novelty; 
	//normalization 
        s = (double)(s+1.2)/3;
	return s;
}
void greeadyCommunityConstraint(double v, map<int, IndexedRealVector > reviewersS, map<int, map<int , double > > PRAffinity, list<int> SelectedReviewers, list<int> papers,string output, int paperQuota , int reviewerQuota)
{
	//cout<<"Rcount is: "<<SelectedReviewers.size()<<endl;
	 ofstream out(output.c_str());
	 map<int , int> RCapacity;
	 map<int , list<int> > assignedRToP;
	 for(list<int>::iterator sr = SelectedReviewers.begin(); sr != SelectedReviewers.end(); sr++)
	 {
		 RCapacity[*sr] = 0;
	//	 cout<<"Capacity of reviwer "<<i<<" is "<<RCapacity[i]<<endl;
	 }
	 // cout<<"Num of topics are : "<<tit->first<<endl;
	 for(list<int>::iterator pit = papers.begin(); pit != papers.end(); pit++)
	 {
		//cout<<*pit<<endl;
		 int rank = 0;
		 while(assignedRToP[*pit].size() < paperQuota)
		 {
			 double max = 0;
			 double srit = -1;
			 for(list<int>::iterator sr = SelectedReviewers.begin(); sr != SelectedReviewers.end(); sr++)
			 {
				if(find(assignedRToP[*pit].begin(), assignedRToP[*pit].end(), *sr) == assignedRToP[*pit].end())
				{
									
					double rscore = score(v,*pit,*sr, assignedRToP[*pit], PRAffinity, reviewersS);
					//cout<<"score is :"<<rscore<<endl;	
					if(rscore > max)
					{
						max = rscore;
						srit = *sr;
					}
				}
					
			 }
			  if(RCapacity[srit] < reviewerQuota)
			  {
			//		 cout<<*pit <<" Q0 "<<srit<< " "<<rank+1<<" "<<max<<" EXP"<<endl;
					 out<<*pit <<" Q0 "<<srit<< " "<<rank+1<<" "<<max<<" EXP"<<endl;
					 RCapacity[srit]++;
					 assignedRToP[*pit].push_back(srit);
					 rank++;
					 if(RCapacity[srit] >= reviewerQuota)
					 {
						 SelectedReviewers.remove(srit);
					 }
			  }
		   }
	 }
	 out.close();
	
}

void communityBasedEF(map<int , int> pClusters, map<int , list<int> > docCluster, string smoothingMethod_, double lambda, double beta,string output,string clusterIndex, string OutputDisplay, int PaperQuota, string allout,list<int> SelectedReviewers, string status,double alpha, string pIndexPath, string rwIndexPath, string prIndexPath)
{

	Index* pIndex = IndexManager::openIndex(pIndexPath.c_str());
	Index* rwIndex = IndexManager::openIndex(rwIndexPath.c_str());
	Index* prIndex = IndexManager::openIndex(prIndexPath.c_str());

	Index * cIndex = IndexManager::openIndex(clusterIndex.c_str());
	ofstream out(output.c_str());
	ofstream allOut(allout.c_str());
	
	map<int , map<int , double> > termClusterProb;
	map<int , map<int , double> > terrmDocProb;
	map<int , map<int , double> > terrmPaperProb;
	map<int , double> collectionProb;
	map<int , int> clusterLength;
	int qid;
	
	///Set up part
	int termID_papers;
	for(termID_papers = 1; termID_papers <= pIndex->termCountUnique(); termID_papers++)
	{
		if(status == "all")
		{
			
			DocInfoList* pList = pIndex->docInfoList(termID_papers);
				pList->startIteration();
				
				DocInfo *pEntry;
				while(pList->hasMore())
				{
					pEntry = pList->nextEntry();
					terrmPaperProb[termID_papers][pEntry->docID()] = (double) pEntry->termCount() / pIndex->docLength(pEntry->docID());
				}
				delete pList;
			
			if(cIndex->term(pIndex->term(termID_papers)) > 0)
			{
				DocInfoList* clutList = cIndex->docInfoList(cIndex->term(pIndex->term(termID_papers)));
				clutList->startIteration();
				
				DocInfo *cEntry;
				while(clutList->hasMore())
				{
					cEntry = clutList->nextEntry();
					termClusterProb[termID_papers][atoi((cIndex->document(cEntry->docID())).c_str())] = cEntry->termCount(); //(double) cEntry->termCount() / cIndex->docLength(cEntry->docID());
				}
				delete clutList;
			}
			if(rwIndex->term(pIndex->term(termID_papers)) > 0)
			{
				DocInfoList* rwList = rwIndex->docInfoList(rwIndex->term(pIndex->term(termID_papers)));
				rwList->startIteration();
				
				DocInfo *rEntry;
				while(rwList->hasMore())
				{
					rEntry = rwList->nextEntry();
					terrmDocProb[termID_papers][rEntry->docID()] = (double) rEntry->termCount() / rwIndex->docLength(rEntry->docID());
				}
				delete rwList;
				
				//collectionProb[termID_papers] = (double) prIndex->termCount(prIndex->term(pIndex->term(termID_papers))) / prIndex->termCount();

				collectionProb[termID_papers] = (double) rwIndex->termCount(rwIndex->term(pIndex->term(termID_papers))) / rwIndex->termCount();
			}
		}
			
	}
	
	//cout<<"Num of query is : "<<pIndex->docCount()<<endl;
	for(qid = 1; qid<= pIndex->docCount(); qid++)
	{
		//cout<<"query is : "<<qid<<endl;
		int docID;
		lemur::api::IndexedRealVector pqd_all;
		
		for(docID = 1; docID <= rwIndex->docCount(); docID++)
		{
//cout<<"Document is : "<<rwIndex->document(docID)<<endl;
			list<int>::iterator sit = find(SelectedReviewers.begin(), SelectedReviewers.end(), atoi((rwIndex->document(docID)).c_str()));
			if(sit != SelectedReviewers.end())
			{
					double pqd = 0.0;
					int nd = rwIndex->docLength(docID);   
					//cout<<docID<<" "<<nd<<endl;     
					TermInfoList *termList2 = pIndex->termInfoList(qid);
					//cout<<qid<<endl;
					termList2->startIteration();   
					lemur::api::TermInfo* tEntry2;
					while (termList2->hasMore()) 
					{
						
						tEntry2 = termList2->nextEntry();

					 if(rwIndex->term(pIndex->term(tEntry2->termID())) > 0)
					 {

			 
						const lemur::api::TERMID_T& termID = tEntry2->termID(); 
			

						const double& ptc2 = collectionProb[termID];//pt_cluster2[termID];
						const double& pt_theta_d = terrmDocProb[termID][docID];
						int cid_p = pClusters[atoi((pIndex->document(qid)).c_str())];
					
						double ptq = 0.0;
						if(cid_p > 0)
						{
						ptq = (alpha) *(terrmPaperProb[termID][qid]) + (1-alpha)*((double) termClusterProb[termID][cid_p]/cIndex->docLength(cIndex->document(to_string(cid_p))));
						}
						else
						{
						
							ptq = (alpha)*(terrmPaperProb[termID][qid]) + (1-alpha)*ptc2;
						}
						double ptd; // p(t|\theta_d) 
				
						double ptc1 = 0.0;
						double c_l = 0.0;
						for(list<int>::iterator fit = docCluster[atoi((rwIndex->document(docID)).c_str())].begin(); fit != docCluster[atoi((rwIndex->document(docID)).c_str())].end(); fit++)
						{
						//	if(ptc1 < termClusterProb[termID][*fit])
								ptc1 += termClusterProb[termID][*fit];    //pt_cluster1[termID];
								c_l += cIndex->docLength(cIndex->document(to_string(*fit)));
							
						}
						
						ptc1 = (double) ptc1/c_l;
						ptd = (lambda) * (pt_theta_d) + (1-lambda)*(beta*ptc1 + (1-beta)*ptc2);
						//cout<<"term "<<pIndex->term(tEntry2->termID())<<" in reviewer "<<rwIndex->document(docID)<<" With Comm "<<ptd<<" without comm "<<ptd_withoutComm<<" "<<endl;
//cout<<"doc id is : "<<docID<<" "<<ptc1<<" "<<ptc2<<" "<<pt_theta_d<<endl;
						pqd += (double) ptq * log(ptd);
						
					}

				  }
				pqd_all.PushValue(docID, pqd );   
		   }
		}
		
		pqd_all.LogToPosterior();
		
		// sort 
		pqd_all.Sort();  
		
		if(OutputDisplay == "Constrained" || OutputDisplay == "Both")
		{
			int rank = 1;
			for ( int i = 0; i < pqd_all.size(); i++ ) 
			{
				const double& pqd = pqd_all[i].val;
				int resDocID = pqd_all[i].ind;
				list<int>::iterator fit = find(SelectedReviewers.begin(), SelectedReviewers.end(), atoi((rwIndex->document(resDocID)).c_str()));
				if(fit != SelectedReviewers.end())
				{
					allOut<<pIndex->document(qid) <<" Q0 "<<rwIndex->document(resDocID)<< " " <<rank<<" "<<pqd<<" EXP"<<endl;
					rank++;
				}
			 }
		 }
		 if(OutputDisplay == "Both" || OutputDisplay == "Unconstrained" )
		 {
			 int rank = 0;
			for ( int i = 0; i < pqd_all.size(); i++ ) 
			{
				const double& pqd = pqd_all[i].val;
				int resDocID = pqd_all[i].ind;
				if(rank < PaperQuota)
				{
						out<<pIndex->document(qid) <<" Q0 "<<rwIndex->document(resDocID)<< " " <<rank+1<<" "<<pqd<<" EXP"<<endl;
						rank++;
				}
			 }
		 }
	}
	out.close();
	allOut.close();
	delete pIndex;
	delete rwIndex;
	delete prIndex;
	delete cIndex;
}
void readClusters(string out , map<int , map<double , list<int> > >&clusters, map<int , list<int> >& pClusters)
{
	ifstream fout(out.c_str());

	string line;
	while(getline(fout, line))
	{
		istringstream iss(line);
		int did,cid;
		double p;
		iss >>did>>cid>>p;
		clusters[cid][p].push_back(did);
		pClusters[did].push_back(cid);
	}
	fout.close();

}
void SelectReviewers(list<int>& SelectedReviewers, string indexPath, string status, int Rcount)
{
	Index * rwIndex = IndexManager::openIndex(indexPath.c_str());
	
	int docCounts = rwIndex->docCount();
	if(status == "all")
	{
		for(int docID = 1; docID <= docCounts ; docID++)
		{
			SelectedReviewers.push_back(atoi((rwIndex->document(docID)).c_str()));
		}
	}
	else
	{
	
		srand(time(NULL));
		while(SelectedReviewers.size() < Rcount)
		{
			int docid = rand() % docCounts + 1;
			list<int>::iterator fit = find(SelectedReviewers.begin(), SelectedReviewers.end(), atoi((rwIndex->document(docid)).c_str()));
			if(fit == SelectedReviewers.end())
			{
				SelectedReviewers.push_back(atoi((rwIndex->document(docid)).c_str()));
			}
		}
	}
	delete rwIndex;
}
void readAssoc(string in , map<int , list<int> >& assoc)
{
	ifstream fout(in.c_str());

	string line;
	while(getline(fout, line))
	{
		istringstream iss(line);
		int rid,pid;
		double p;
		iss >>rid>>pid;
		assoc[pid].push_back(rid);
	}
	fout.close();
}
void readRClusters(map<int , list<int> > assoc,map<int , list<int> > paperClusters,map<int , list<int> >&RClusters)
{
	for(map<int , list<int> >::iterator pit = paperClusters.begin(); pit != paperClusters.end(); pit++)
	{
		map<int , list<int> >::iterator fit = assoc.find(pit->first);
		if(fit != assoc.end())
		{
			for(list<int>::iterator  rit = fit->second.begin(); rit != fit->second.end(); rit++)
			{
				RClusters[*rit].insert(RClusters[*rit].end(), pit->second.begin(), pit->second.end());
			}
		}
	}
	///remove repeating
	/*
	for(map<int , list<int> >::iterator rit = RClusters.begin(); rit != RClusters.end(); rit++)
	{
		(rit->second).sort();
		(rit->second).unique();
	}
	*/
}
void writeDocAsQuery(string writing,string indexPath)
{
	Index * rwIndex = IndexManager::openIndex(indexPath.c_str());
	
	int docCounts = rwIndex->docCount();
	ofstream fout(writing.c_str());
	
	for(int docID = 1; docID <= docCounts ; docID++)
	{
		fout<<"<DOC "<<rwIndex->document(docID)<<">"<<endl;
		TermInfoList *termList2 = rwIndex->termInfoList(docID);
		termList2->startIteration();   
		lemur::api::TermInfo* tEntry2;
		while (termList2->hasMore()) 
		{
			tEntry2 = termList2->nextEntry(); 
			
			fout<<rwIndex->term(tEntry2->termID())<<" ";
		}
		fout<<"\n</DOC>"<<endl;
		delete termList2;
	}
	fout.close();
	delete rwIndex;
}
void normalize(map<int , map<int , double> > rs, map<int , map<int , double> >& nRS)
{

	for(map<int , map<int , double> >::iterator pit = rs.begin(); pit != rs.end(); pit++)
	{
		double max = rs[pit->first][1];
		double min = rs[pit->first][1];
		for(map<int , double>::iterator rit = pit->second.begin(); rit != pit->second.end(); rit++)
		{
			if(rit->second < min)
			{
				min = rit->second;
			}
			if(rit->second > max)
			{
				max = rit->second;
			}
		}
		for(map<int , double>::iterator rit = pit->second.begin(); rit != pit->second.end(); rit++)
		{
			nRS[pit->first][rit->first] = (double)(rit->second - min)/(max -min);
		}
	
	}
}
int main(int argc, char* argv[])
{
	//scoreCollection();
	map<int , map<double , list<int> > > clusters;
	map<int , list<int> > paperClusters;
	map<int , int> queryCluster;
	//paper and their authors
	map<int , list<int> > assoc;
	map<int , list<int> > RClusters;
	map<int, map<int , double> > PRAffinity;
	map<int, map<double , list<int> > > SortedPRAffinity;
	map<int , IndexedRealVector> reviewersS;
	list<int> qpapers;
	//map<int , map<int , double> > normRS;
	list<int> SelectedReviewers;
	
	map<int , vector<double> > paperTopics;
	map<int , vector<double> > meanClusterVectors;
	map<int , vector<double> > queryTopics;
	//path to query file of docs
       writeDocAsQuery(argv[1],argv[2]);
    //path to confing file
      string hash = argv[20];
      //string filename = argv[3];
      //string command = "RetEval " + filename;
  //  cout<<"command is : "<<command<<endl;
     //int returnval = system(command.c_str());
    
	//path to output of kl
	readAllAffinityRS(argv[4],reviewersS);
	readClusters(argv[5],clusters,paperClusters);
	readAssoc(argv[6], assoc);
	readRClusters(assoc,paperClusters,RClusters);

	FindClusterOfEachQuery(qpapers,queryCluster,argv[7],hash);
		
	///Selecting reviewers
	SelectReviewers(SelectedReviewers,argv[2],argv[8],atoi(argv[9]));
	//cout<<SelectedReviewers.size()<<endl;
	// Time estimation
	time_t t_u = time(NULL);
	time_t t_c = 0;

	communityBasedEF(queryCluster,RClusters,"jm",atof(argv[10]),atof(argv[11]),argv[12],argv[7],argv[13],atoi(argv[14]),argv[15],SelectedReviewers, argv[8],atof(argv[16]),"Index/indexFile/" + hash + "/queries_index.key","Index/indexFile/profiles_index.key","Index/indexFile/publications_index.key");
	t_u = time(NULL) - t_u;
	/// This is for constrained based problem.
	/*if((strcmp(argv[13],"Constrained") == 0) || (strcmp(argv[13],"Both") == 0))
	{   
		//cout<<"before read affinity"<<endl;
		readAllAffinity(argv[15], PRAffinity);
		t_c = time(NULL);	
		greeadyCommunityConstraint(atof(argv[17]),reviewersS,PRAffinity,SelectedReviewers,qpapers,argv[18],atoi(argv[14]),atoi(argv[19]));
		t_c = time(NULL) - t_c;	
	}*/
	cout<<"all time is : "<<t_u + t_c<<" unconstrained time is : "<<t_u<<" constrained time is : "<<t_c<<endl;
	
	
}
