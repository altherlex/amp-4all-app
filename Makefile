ifndef NAME
NAME = googleamp
endif

ifndef SPECFILE
SPECFILE = $(firstword $(wildcard *.spec))
endif

ifndef VERSION
VERSION = $(shell git describe --abbrev=0 --tags | sed -e 's/^v//' | cut -f1 -d'-')
endif

ifndef REVISION
REVISION = $(shell git describe --abbrev=0 --tags | sed -e 's/^v//' | cut -f2 -d'-')
endif

ifndef RANGE
RANGE = $(shell git show `git describe --abbrev=0 --tags` | grep KOJI | perl -ne '/RANGE:(\w+\.\.\w+)/; print $1')
endif

ifndef WORKDIR
WORKDIR = $(shell pwd)
endif

ifndef SRCRPMDIR
SRCRPMDIR = $(WORKDIR)
endif

ifndef BUILDDIR
BUILDDIR = $(WORKDIR)
endif

ifndef RPMDIR
RPMDIR = $(WORKDIR)
endif

ifndef SOURCEDIR
SOURCEDIR = $(shell pwd)
endif

rpmname:
	@echo $(NAME)-$(VERSION)-$(REVISION)

info:
	echo $(NAME)_$(VERSION)-$(REVISION)

clean:
	rm -f *~ $(NAME)*.bz2 $(NAME)*.src.rpm
	rm -rf .$(NAME)-$(VERSION)

git-clean:
	@git clean -d -q -x

prepare-spec:
	sed -i $(SED_INLINE_BY_OS) 's/#VERSION#/$(VERSION)/' $(WORKDIR)/$(SPECFILE) | true
	sed -i $(SED_INLINE_BY_OS) 's/#REVISION#/$(REVISION)/' $(WORKDIR)/$(SPECFILE) | true
	@rm -f $(WORKDIR)/$(SPECFILE).tmp
	@echo "* `date +"%a %b %d %Y"`" Koji Build Server >> $(SPECFILE)
	@echo "" >> $(SPECFILE)
	for tag in `git tag -l`; do \
	  if [ "`echo $$tag | cut -c1`" != "v" ]; then \
	    continue ; \
	  fi ; \
	  git show --pretty=format:"|__ %ad by %an <%ae>" --quiet $$tag | sed 's/^Tagger.*//' | sed 's/^tag v.*//' | uniq >> $(SPECFILE) | true ; \
	  echo "" >> $(SPECFILE) ; \
	done

tarball: clean prepare-spec
	@git archive --format=tar --prefix=$(NAME)-$(VERSION)/ HEAD | bzip2 > $(NAME)-$(VERSION)-$(REVISION).tar.bz2

sources: tarball
