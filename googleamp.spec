##
# RPM Spec for publiabril, a RoR app with MySQL
##

%define debug_package %{nil}
%define __os_install_post %{nil}

%define _templatedir %{_datadir}/%{name}/template

Summary: GoogleAmp
Name: googleamp
Version: #VERSION#
Release: #REVISION#.el6
Source: %{name}-%{version}-#REVISION#.tar.bz2
Group: Applications/Internet
URL: http://amp.abril.com.br/
License: Proprietary
Distribution: Abril
Vendor: Abril
Packager: Daniel Faria <daniel.faria@abril.com.br> 
Buildroot: %{_tmppath}/%{name}-%{version}-root
AutoReqProv: no

Requires: php-mbstring

%define appdir /opt/abril/%{name}
%define username googleamp
%define groupname googleamp
%define userid 1000
%define groupid 1000

%description
Google Amp

%prep
%setup -q

%build

%install
rm -rf $RPM_BUILD_ROOT
mkdir -p $RPM_BUILD_ROOT/opt/abril
mkdir -p $RPM_BUILD_ROOT/etc/logrotate.d

cp -a $RPM_BUILD_DIR/%{name}-%{version} $RPM_BUILD_ROOT%{appdir}
rm -f $RPM_BUILD_ROOT%{appdir}/%{name}.spec
rm -f $RPM_BUILD_ROOT%{appdir}/Makefile

%pre
rm -rf %{appdir}
getent group %{groupname} > /dev/null || groupadd -g %{groupid} -r %{groupname}
getent passwd %{username} > /dev/null || \
  useradd -r -u %{userid} -g %{groupname} -s /sbin/nologin \
    -d %{appdir} -c "%{name} User" %{username}
exit 0

%post
echo "restarting php-fpm"
service php-fpm restart

%postun

%clean
rm -rf $RPM_BUILD_ROOT

%files
%defattr(0644,googleamp,googleamp, 0755)
%{appdir}

%changelog
