# OPENHAB 4: UI2FILES

Command line tool to migrate UI config to files for **OpenHAB v. 4** (semantic model compatible)

This tool is writter in **PHP 8** and can be run as a docker container in Windows, using runme.bat. Otherwise you could run by yourself using a local **PHP 8** installation

It generate things and items files, reading bridges, things, channels, links, location, equipments, groups, points and items from OpenHAB 4 configuration using API Rest.

- .things files (bridges, things and channels) will be generated in *output_folder*/things
- .items files (locations, equipments, groups, points, items and links) will be generated in *output_folder*/items

In the *output_folder* there are other 2 files

- 98_addons.txt: list of installed addons as a reference
- 99_addonsconfig.txt: config variables for installed addons

## Usage

- Copy *params.php.template* in *params.php* and populate config variables according to your setup
- Run *runme.bat*
- Files will be generated in output folder
