import java.io.BufferedReader;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.IOException;
import java.util.Vector;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

public class Config {
	
	private JSONObject config;
	
	/**
	 * Constructs a new Config.  The constructor reads in the config file
	 */
	public Config(){
		this.readConfig();
	}
	
	/**
	 * Returns a Vector of the different types used in the Graph
	 * @return a Vector of the different types used in the Graph
	 */
	public Vector<String> getTypes(){
		Vector<String> returnTypes = new Vector<String>();
		try{
			JSONArray types = this.config.getJSONArray("types");
			for(int i = 0; i < types.length(); i++){
				returnTypes.add(types.getString(i));
			}
		} catch (JSONException e) {
			
		}
		return returnTypes;
	}
	
	/**
	 * Returns a Vector containing all the groups for the specified type
	 * @param type The type of node to get the groups for
	 * @return a Vector containing all the groups for the specified type
	 */
	public Vector<JSONObject> getGroups(String type){
		Vector<JSONObject> returnGroups = new Vector<JSONObject>();
		try {
			JSONObject metas = this.config.getJSONObject("meta");
			JSONObject tMetas = metas.getJSONObject(type);
			JSONArray groups = tMetas.getJSONArray("groups");
			for(int i = 0; i < groups.length(); i++){
				returnGroups.add(groups.getJSONObject(i));
			}
		} catch (JSONException e) {
			
		}
		return returnGroups;
	}
	
	/**
	 * Returns a Vector containing all the tests for the specified type
	 * @param type The type of node to get the groups for
	 * @return Vector<JSONObject> Returns a Vector containing all the tests for the specified type
	 */
	public Vector<JSONObject> getTests(String type){
		Vector<JSONObject> returnTests = new Vector<JSONObject>();
		try {
			JSONObject tests = this.config.getJSONObject("tests");
			JSONArray tTests = tests.getJSONArray(type);
			for(int i = 0; i < tTests.length(); i++){
				returnTests.add(tTests.getJSONObject(i));
			}
		} catch (JSONException e) {
			e.printStackTrace();
		}
		return returnTests;
	}
	
	/**
	 * Reads in config.json and stores it in this.config
	 */
	private void readConfig(){
		File file = new File("config.json");
		BufferedReader in;
		try {
			in = new BufferedReader(new FileReader(file));
			String string = "";
		    String str;
		    while ((str = in.readLine()) != null) {
		        string += str;
		    }
		    this.config = new JSONObject(string);
		    in.close();
		} catch (FileNotFoundException e) {
			System.err.println("No configuration file was found");
		} catch (IOException e) {
			System.err.println("There was a problem reading the config.json");
		} catch (JSONException e) {
			System.err.println("There was a problem parsing config.json: " + e.getLocalizedMessage());
		}
	}
	
}
