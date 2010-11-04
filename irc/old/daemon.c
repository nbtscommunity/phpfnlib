#include <unistd.h>

int main() {
	if(fork()) {
		daemon(1,0);
		execl("/usr/bin/php", "-q", "./daemon.php", NULL);
	}
}
	
