"""
longest common subsequence algorithm

the algorithm is describe in "An O(ND) Difference Algorithm and its Variation"
by Eugene W. MYERS

As opposed to the algorithm in difflib.py, this one doesn't require hashable
elements 
"""
__revision__ = '$Id: mydifflib.py,v 1.6 2002/09/02 15:19:52 syt Exp $'

def lcs2(X, Y, equal):
    """
    apply the greedy lcs/ses algorithm between X and Y sequence
    (should be any Python's sequence)
    equal is a function to compare X and Y which must return 0 if
    X and Y are different, 1 if they are identical
    return a list of matched pairs in tuplesthe greedy lcs/ses algorithm
    """
    N, M = len(X), len(Y)
    if not X or not Y :
        return []
    max = N + M
    v = [0 for i in xrange(2*max+1)]
    common = [[] for i in xrange(2*max+1)]
    for i in xrange(max+1):
        for j in xrange(-i, i+1, 2):
            if j == -i or j != i and v[j-1] < v[j+1]:
                x = v[j+1]
                common[j] = common[j+1][:]
            else:
                x = v[j-1] + 1
                common[j] = common[j-1][:]
                
            y = x - j
            while x < N and y < M and equal(X[x], Y[y]):
                common[j].append((X[x], Y[y]))
                x += 1 ; y += 1

            v[j] = x
            if x >= N and y >= M:
                return common[j]

def lcsl(X, Y, equal):
    return len(lcs2(X,Y,equal))

def quick_ratio(a,b):
    """
    optimized version of the standard difflib.py quick_ration
    (without junk and class)
    Return an upper bound on ratio() relatively quickly.
    """
    # viewing a and b as multisets, set matches to the cardinality
    # of their intersection; this counts the number of matches
    # without regard to order, so is clearly an upper bound
    if not a and not b:
        return 1
    fullbcount = {}
    for elt in b:
        fullbcount[elt] = fullbcount.get(elt, 0) + 1
    # avail[x] is the number of times x appears in 'b' less the
    # number of times we've seen it in 'a' so far ... kinda
    avail = {}
    availhas, matches = avail.has_key, 0
    for elt in a:
        if availhas(elt):
            numb = avail[elt]
        else:
            numb = fullbcount.get(elt, 0)
        avail[elt] = numb - 1
        if numb > 0:
            matches = matches + 1
    return 2.0 * matches / (len(a) + len(b))

try:
    import psyco
    psyco.bind(lcs2)
except Exception, e:
    pass

def test():
    import time
    t = time.clock()
    quick_ratio('abcdefghijklmnopqrst'*100, 'abcdefghijklmnopqrst'*100)
    print 'quick ratio :',time.clock()-t
    lcs2('abcdefghijklmnopqrst'*100, 'abcdefghijklmnopqrst'*100, lambda x, y : x==y)
    print 'lcs2 :       ',time.clock()-t
    quick_ratio('abcdefghijklmno'*100, 'zyxwvutsrqp'*100)
    print 'quick ratio :',time.clock()-t
    lcs2('abcdefghijklmno'*100, 'zyxwvutsrqp'*100, lambda x, y : x==y)
    print 'lcs2 :       ',time.clock()-t
    quick_ratio('abcdefghijklmnopqrst'*100, 'abcdefghijklmnopqrst'*100)
    print 'quick ratio :',time.clock()-t
    lcs2('abcdefghijklmnopqrst'*100, 'abcdefghijklmnopqrst'*100, lambda x, y : x==y)
    print 'lcs2 :       ',time.clock()-t
    quick_ratio('abcdefghijklmno'*100, 'zyxwvutsrqp'*100)
    print 'quick ratio :',time.clock()-t
    lcs2('abcdefghijklmno'*100, 'zyxwvutsrqp'*100, lambda x, y : x==y)
    print 'lcs2 :       ',time.clock()-t

    
if __name__ == '__main__':
    print lcsl('abcde', 'bydc', lambda x, y : x==y)
    for a in lcs2('abcde', 'bydc', lambda x, y : x==y):
        print a
    print lcsl('abacdge', 'bcdg', lambda x, y : x==y)
    for a in lcs2('abacdge', 'bcdg', lambda x, y : x==y):
        print a
        
