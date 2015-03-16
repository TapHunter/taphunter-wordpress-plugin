.PHONY: dist

all: dist

dist:
	rm -f taphunter-wordpress-x.y.z.zip
	zip -r taphunter-wordpress-x.y.z.zip taphunter
