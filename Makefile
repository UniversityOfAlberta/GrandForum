#
# This Makefile is used to test some MediaWiki functions. If you
# want to install MediaWiki, point your browser to ./config/
#

# Configuration:
PROVE_BIN="prove"
DOC_BIN="phpdoc"
PHP_DOCS_IGNORE=-i "AccessControls/, AnnokiControl/, SociQL/, MaintenanceService/, VisualQueryEditor/, TemplateEditor/, TextReplace/, Calendar/"

# Describe our tests:
BASE_TEST=$(wildcard t/*.t)
INCLUDES_TESTS=$(wildcard t/inc/*t)
MAINTENANCE_TESTS=$(wildcard t/maint/*t)

# Build groups:
FAST_TESTS=$(BASE_TEST) $(INCLUDES_TESTS)
ALL_TESTS=$(BASE_TEST) $(INCLUDES_TESTS) $(MAINTENANCE_TESTS)

test: t/Test.php
	$(PROVE_BIN) $(ALL_TESTS)

fast: t/Test.php
	$(PROVE_BIN) $(FAST_TESTS)

maint:
	$(PROVE_BIN) $(MAINTENANCE_TESTS)

verbose: t/Test.php
	$(PROVE_BIN) -v $(ALL_TESTS) | egrep -v '^ok'

docs: extensions/
	$(DOC_BIN) $(PHP_DOCS_IGNORE) --title "The GRAND Forum" -d extensions/ -t /local/data/www-root/html/docs
