Name: recallonbusy
Version: 1.0.0
Release: 1%{?dist}
Summary: Recall On Busy for NethVoice14
Group: Network
License: GPLv2
Source0: %{name}-%{version}.tar.gz
Source1: recallonbusy.tar.gz
BuildRequires: nethserver-devtools
Buildarch: noarch
Requires: nethserver-freepbx

%description
Recall On Busy for NethVoice14

%prep
%setup

%build
perl createlinks

%install
rm -rf %{buildroot}
(cd root; find . -depth -print | cpio -dump %{buildroot})

mkdir -p %{buildroot}/usr/src/nethvoice/modules
mv %{S:1} %{buildroot}/usr/src/nethvoice/modules/

%{genfilelist} %{buildroot} \
> %{name}-%{version}-filelist


%clean
rm -rf %{buildroot}

%files -f %{name}-%{version}-filelist
%defattr(-,root,root,-)

%doc
%dir %{_nseventsdir}/%{name}-update

%changelog
* Tue Oct 05 2021 Stefano Fancello <stefano.fancello@nethesis.it> - 1.0.0-1
- First Recall on Busy package nethesis/dev#6066


