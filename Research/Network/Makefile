FILES=src/ bin/ "install/" config.json.example Network.jar libs/commons/ libs/jung/ libs/json/ cache/ output/ docs/ generate.sh generate.bat Makefile Network.R

all: Network.jar rlibs
Network.jar: src/*.java install/MANIFEST.MF
	javac -O -cp .:`find * -name "*.jar" | tr "\n" ":"` src/*.java -d bin/
	jar cfm "Network.jar" install/MANIFEST.MF -C bin/ . 
rlibs:
	Rscript install/Install.R
tar: Network.jar cleandata
	tar -czf Network.tar.gz $(FILES)
zip: Network.jar cleandata
	zip -q -r Network.zip $(FILES)
clean: cleandata
	rm -fr bin/* *.jar Network.tar.gz Network.zip libs/R/*
cleandata:
	rm -fr output/* cache/* Network.tar.gz Network.zip
