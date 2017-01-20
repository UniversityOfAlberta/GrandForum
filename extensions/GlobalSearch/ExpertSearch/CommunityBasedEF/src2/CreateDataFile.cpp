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

using namespace std;
using namespace lemur::api;
using namespace boost::algorithm;

void readTopicFileAsCluster(string rp, string cs_data, string cs_newFormat, string dataset)
{
	Index * index_;
	if(dataset == "pubMed")
	{

		index_ = IndexManager::openIndex("/cshome/mmirzaei/Desktop/CommunityBasedPaper/Index/pubMed/reviewers/docsIndex_pubMed_WithoutStopWords.key");
	}
	else
	{
		if(dataset == "SIGIR")
		{
			index_ = IndexManager::openIndex("/cshome/mmirzaei/Desktop/CommunityBasedPaper/Index/SIGIR/reviewers/docsIndex2_WithoutStopWords.key");	
		}
		else
		{		
			index_ = IndexManager::openIndex("/mnt/hgfs/Desktop/MultipleEF/index/docsIndex_SD_WithoutStopWords.key");
			
		}
	}
	
	ofstream cs_data_out(cs_data.c_str());
	ofstream cs_newFormat_out(cs_newFormat.c_str());
	
	map<string , list<string> > eTopics;
	
	ifstream tuk(rp.c_str());
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
			eTopics[tokens[0]].push_back(topics2);
		}
		
	}
	tuk.close();
	
	map<string , list<string> > TopicsReviewers;
	for(map<string , list<string> >::iterator it = eTopics.begin(); it != eTopics.end(); it++)
	{
		for(list<string>::iterator lit = it->second.begin(); lit != it->second.end(); lit++)
		{
			string Top = *lit;
			string rw = it->first;
			Top.erase(std::remove(Top.begin(),Top.end(),'\n'),Top.end());
			rw.erase(std::remove(rw.begin(),rw.end(),'\n'),rw.end());
			TopicsReviewers[Top].push_back(rw);
		}
	}
	
	///writing cluster files
	int clusterID = 1;
	for(map<string , list<string> >::iterator it = TopicsReviewers.begin(); it != TopicsReviewers.end(); it++)
	{
		cout<<" Cluster "<<clusterID<<" for topic "<<it->first<<" has "<<it->second.size()<<" number of points"<<endl;
		cs_data_out << "<DOC>"<< endl;
		cs_data_out << "<DOCNO>" << clusterID << "</DOCNO>" << endl;
		cs_data_out << "<TEXT>" << endl;
		for(list<string>::iterator it2 = it->second.begin(); it2 != it->second.end(); it2++)
		{
			cs_newFormat_out<<*it2<<" "<<clusterID<<" "<<1<<endl;
			
			lemur::api::TermInfoList* termList = index_->termInfoListSeq(index_->document(*it2));
			termList->startIteration();   
			lemur::api::TermInfo* tEntry;
			while (termList->hasMore()) {
				
				tEntry = termList->nextEntry(); 
				string term = index_->term(tEntry->termID());
				cs_data_out<<term<<" ";
			}
			delete termList;
			cs_data_out << "\n";
		}
		cs_data_out<< "</TEXT>" << endl;
		cs_data_out<<"</DOC>"<<endl; 
		clusterID++;
		
	}
	cs_data_out.close();
	cs_newFormat_out.close();
	delete index_;
			
}

void readClusterFile(string cs , string cs_data, string cs_newFormat,string dataset)
{
	Index * index_;
	
	if(dataset == "pubMed")
	{
		index_ = IndexManager::openIndex("/cshome/mmirzaei/Desktop/CommunityBasedPaper/Index/pubMed/reviewers/docsIndex_pubMed_WithoutStopWords.key");
	}
	else
	{
		if(dataset == "SIGIR")
		{
			index_ = IndexManager::openIndex("/cshome/mmirzaei/Desktop/CommunityBasedPaper/Index/SIGIR/reviewers/docsIndex2_WithoutStopWords.key");	
		}
		else
		{		
			index_ = IndexManager::openIndex("/mnt/hgfs/Desktop/MultipleEF/index/docsIndex_SD_WithoutStopWords.key");
			
		}
	}
	
	ifstream cs_in(cs.c_str());
	
	ofstream cs_data_out(cs_data.c_str());
	ofstream cs_newFormat_out(cs_newFormat.c_str());
	
	//map<string , string > docClust;
	map<int , list<int> > clust;
	string line;
	int did = 1;
	int cid2 = 1;
	while(getline(cs_in, line))
	{
		istringstream iss(line);
		string cid;
		while(getline(iss,cid,','))
		{
			//docClust[did] = cid;
			if(atoi(cid.c_str()) != 0)
			{
				/*
				if(dataset == "pubMed" && did >=3)
					clust[cid2].push_back(did+1);
				else
					clust[cid2].push_back(did);
				cid2++;
			}
			else
			{*/
				if(dataset == "pubMed" && did >=3)
					clust[atoi(cid.c_str())].push_back(did+1);
				else
					clust[atoi(cid.c_str())].push_back(did);
			}
			did++;
			
		}
	}
	cs_in.close();
	ifstream cs_in2(cs.c_str());
	did = 1;
	while(getline(cs_in2, line))
	{
		istringstream iss(line);
		string cid;
		while(getline(iss,cid,','))
		{
			//docClust[did] = cid;
			if(atoi(cid.c_str()) == 0)
			{
				while(clust.find(cid2) != clust.end())
				{
					cid2++;
				}
				if(dataset == "pubMed" && did >=3)
					clust[cid2].push_back(did+1);
				else
					clust[cid2].push_back(did);
				cid2++;
				
			}
			did++;
			
		}
	}
	cs_in2.close();
	for(map<int , list<int> >::iterator it = clust.begin(); it != clust.end(); it++)
	{
		cout<<" Cluster "<<it->first<<" has "<<it->second.size()<<" number of points"<<endl;
		cs_data_out << "<DOC>"<< endl;
		cs_data_out << "<DOCNO>" << it->first << "</DOCNO>" << endl;
		cs_data_out << "<TEXT>" << endl;
		for(list<int>::iterator it2 = it->second.begin(); it2 != it->second.end(); it2++)
		{
			cs_newFormat_out<<*it2<<" "<<it->first<<" "<<1<<endl;
			
			lemur::api::TermInfoList* termList = index_->termInfoListSeq(index_->document(to_string(*it2)));
			termList->startIteration();   
			lemur::api::TermInfo* tEntry;
			while (termList->hasMore()) {
				
				tEntry = termList->nextEntry(); 
				string term = index_->term(tEntry->termID());
				cs_data_out<<term<<" ";
			}
			delete termList;
			cs_data_out << "\n";
		}
		cs_data_out<< "</TEXT>" << endl;
		cs_data_out<<"</DOC>"<<endl; 
		
	}
	cs_data_out.close();
	cs_newFormat_out.close();
	delete index_;
}
void readPaperClusterFile(string cs, string cs_data ,string indexPath)
{
	Index * index_ = IndexManager::openIndex(indexPath.c_str());
	
	
	ifstream cs_in(cs.c_str());
	
	ofstream cs_data_out(cs_data.c_str());
	
	//map<string , string > docClust;
	map<int , list<int> > clust;
	string line;

	while(getline(cs_in, line))
	{
		istringstream iss(line);
		string pid,cid, score;
		iss >> pid >>cid>>score;
		if(atoi(cid.c_str()) != 0)
		{
			clust[atoi(cid.c_str())].push_back(atoi(pid.c_str()));
		}
			
	}
	cs_in.close();

	for(map<int , list<int> >::iterator it = clust.begin(); it != clust.end(); it++)
	{
		cs_data_out << "<DOC>"<< endl;
		cs_data_out << "<DOCNO>" << it->first << "</DOCNO>" << endl;
		cs_data_out << "<TEXT>" << endl;
		for(list<int>::iterator it2 = it->second.begin(); it2 != it->second.end(); it2++)
		{
			//cout<<*it2<<" "<<it->first<<" "<<1<<endl;
			lemur::api::TermInfoList* termList = index_->termInfoListSeq(index_->document(to_string(*it2)));
			termList->startIteration();   
			lemur::api::TermInfo* tEntry;
			while (termList->hasMore()) {
				
				tEntry = termList->nextEntry(); 
				string term = index_->term(tEntry->termID());
				cs_data_out<<term<<" ";
			}
			delete termList;
			cs_data_out << "\n";
		}
		cs_data_out<< "</TEXT>" << endl;
		cs_data_out<<"</DOC>"<<endl; 
		
	}
	cs_data_out.close();
	delete index_;
}

int main(int argc, char* argv[])
{
	readPaperClusterFile(argv[1],argv[2],argv[3]);
//	readClusterFile(argv[1],argv[2],argv[3],argv[4]);	
//	readTopicFileAsCluster(argv[1],argv[2],argv[3],argv[4]);
}
