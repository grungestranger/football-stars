import sys
from subprocess import check_output

groupname = sys.argv[1]

def write_stdout(s):
    # only eventlistener protocol messages may be sent to stdout
    sys.stdout.write(s)
    sys.stdout.flush()

def main():
    while 1:
        # transition from ACKNOWLEDGED to READY
        write_stdout('READY\n')

        line = sys.stdin.readline()

        headers = dict([ x.split(':') for x in line.split() ])
        data = dict([ x.split(':') for x in sys.stdin.read(int(headers['len'])).split() ])

        if headers['eventname'] == 'PROCESS_STATE_EXITED' and data['groupname'] == groupname:
            check_output(['supervisorctl', 'restart', groupname + ':'])

        # transition from READY to ACKNOWLEDGED
        write_stdout('RESULT 2\nOK')

if __name__ == '__main__':
    main()
