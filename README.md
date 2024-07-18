# OPENHAB 4: UI2FILES

Command line tool to migrate UI config to files for **OpenHAB v. 4** (semantic model compatible)

This tool is writter in **PHP 8** and can be run as a docker container in Windows, using runme.bat. Otherwise you could run by yourself using a local **PHP 8** installation

It generate things and items files and partial services.cfg file, reading bridges, things, channels, links, location, equipments, groups, points, items and addons from OpenHAB 4 configuration using API Rest.

- .things files (bridges, things and channels) will be generated in *output_folder*/things
- .items files (locations, equipments, groups, points, items and links) will be generated in *output_folder*/items
- addons.cfg.append (content must be added to the original addons.cfg file) will be generated in *output_folder*/services.

In the *output_folder* there is 1 file

- XX_addonsconfig.txt: config variables for installed addons as simple reference

## Usage

- Copy *params.php.template* in *params.php* and populate config variables according to your setup
- Run *runme.bat*
- Files will be generated in output folder
