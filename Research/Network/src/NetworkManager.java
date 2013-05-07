import java.io.BufferedWriter;

import java.io.File;
import java.io.FileWriter;
import java.io.IOException;
import java.text.NumberFormat;
import java.util.HashMap;
import java.util.Vector;

import org.json.JSONException;
import org.json.JSONObject;

public class NetworkManager {
	
	private String name;
	private Network network;
	
	/**
	 * Constructs a new NetworkManger (used to output the csv file)
	 * @param name The name of the network
	 * @param network The referenced Network
	 */
	public NetworkManager(String name, Network network){
		this.network = network;
		this.name = name;
	}
	
	/**
	 * Returns the referenced Network
	 * @return the referenced Network
	 */
	public Network getNetwork(){
		return this.network;
	}
	
	/**
	 * Outputs a csv file in the output/data directory
	 */
	public void printCSV(){
		HashMap<String, Vector<Double>> finalResults = new HashMap<String, Vector<Double>>();
		Vector<HashMap<String, Double>> results = this.network.getResults();
		for(int i = 0; i < results.size(); i++){
			HashMap<String, Double> result = results.get(i);
			for(String key : result.keySet()){
				Vector<Double> tuple;
				if(!finalResults.containsKey(key)){
					tuple = new Vector<Double>(results.size());
					finalResults.put(key, tuple);
					for(int x = 0; x < results.size(); x++){
						tuple.addElement(0d);
					}
				}
				else{
					tuple = finalResults.get(key);
				}
				tuple.insertElementAt(result.get(key), i);
				tuple.remove(i + 1);
			}
		}
		FileWriter fstream;
		for(String type : this.network.getNodeTypes()){
			try {
				File dir = new File("output/data/");
				if(!dir.exists()){
					dir.mkdir();
				}
				fstream = new FileWriter("output/data/" + type + "_" + this.name + ".csv");
				BufferedWriter out = new BufferedWriter(fstream);
				NumberFormat nf = NumberFormat.getInstance();
				nf.setMinimumFractionDigits(2);
				nf.setMaximumFractionDigits(2);
				nf.setGroupingUsed(false);
				StringBuilder b = new StringBuilder();
				b.append("Name");
				for(String key : this.network.getMetaLabels(type)){
					b.append("," + key);
				}
				
				b.append(",Between");
				b.append(",Closeness");
				b.append(",PageRank");
				Config config = new Config();
				for(String t : config.getTypes()){
					Vector<JSONObject> groups = config.getGroups(t);
					for(int i = 0; i < groups.size(); i++){
						b.append(",Between." + groups.get(i).getString("id"));
						b.append(",Closeness." + groups.get(i).getString("id"));
						b.append(",PageRank." + groups.get(i).getString("id"));
					}
				}
				
				b.append("\n");
				HashMap<String, Node> nodes = this.network.getNodes(type);
				//Vector<Vector<String>> ranks = this.network.getRanks(type);
				for(String key : finalResults.keySet()){
					if(this.network.getMetas().containsKey(key) && nodes.containsKey(key)){
						b.append("\"" + key + "\"");
						HashMap<String,String> meta = this.network.getMetas().get(key);
						for(String field : this.network.getMetaLabels(type)){
							if(meta.get(field) == null || meta.get(field).compareTo("") == 0){
								b.append(",");
							}
							else{
								b.append("," + '"' + meta.get(field) + '"');
							}
						}
						for(int i = 0; i < finalResults.get(key).size(); i++){
							if(nf.format(finalResults.get(key).get(i)).compareTo("0.00") == 0){
								b.append(",");
							}
							else{
								b.append("," + nf.format(finalResults.get(key).get(i)));
							}
						}
						b.append("\n");
					}
				}
				out.write(b.toString());
				out.close();
			} catch (IOException e) {
				System.err.println("There was a problem writing the file");
			} catch (JSONException e) {
				e.printStackTrace();
			}
		}
	}
}
