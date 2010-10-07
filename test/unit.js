// this is a simple unit test framework ________________________________________
// to register a test do something like this:
/*
  tc("Testname", function()
    {
      // code that should be tested
      test_equal(1, 2); // testers can be used here
    }
  );
*/
//
// to run the tests execute runtests()

// _____________________________________________________________________________

var test_failed = false;

var unittests = new Array();

/**
 * add a test case to the unit test
 *
 * @param nme name of the unit test
 * @param func function that should be tested, to error console.log exception
 * @param stop flag to indicate if the unit test framework should stop
 *             if this testcase fails
 */
function tc(nme, func, stop)
{
  if(stop == null) stop = true;
  unittests.push(new Array(nme, func, stop));              // register unit test
}

/**
 * run the unit tests,
 */
function runtests()
{
  var i;
  for(i=0; i<unittests.length; i++)
  {
    try
    {
      if(test_failed && unittests[i-1][2])      // check if previous test failed
      {
        console.log("Error in test " + (i-1) + ": " + 
          unittests[i-1][0] + " failed.");
        return;
      }
      unittests[i][1]();
    }
    catch(e)
    {
      console.log("Error in test " + i + ": " + unittests[i][0] + " failed.");
      if(unittests[i][2])                                // break test execution
      {
        return;
      }                     
    }
  }
                                          // last test requires special handling
  if(test_failed && unittests[i-1][2])          // check if previous test failed
  {
    console.log("Error in test " + (i-1) + ": " + 
      unittests[i-1][0] + "failed.");
    return;
  }

  console.log(unittests.length + " tests executed successfully.");
  console.log("** No errors detected.");
}

// testers _____________________________________________________________________

function test_equal(var1, var2, msg)
{
  if(msg == null) msg = "";
  if(var1 != var2)
  {
    console.log(msg + ", test_equal failed: " + var1 + " != " + var2);
    test_failed = true;
  }
}

function test_true(var1, msg)
{
  if(msg == null) msg = "";
  if(!var1)
  {
    console.log(msg + ", test_true failed: " + var1);
    test_failed = true;
  }
}

function test_false(var1, msg)
{
  if(msg == null) msg = "";  
  if(var1)
  {
    console.log(msg + ", test_false failed: " + var1);
    test_failed = true;
  }
}

function test_notequal(var1, var2, msg)
{
  if(msg == null) msg = "";
  if(var1 == var2)
  {
    console.log(msg + ", test_notequal failed: " + var1 + " == " + var2);
    test_failed = true;
  }
}

function test_greater(var1, var2, msg)
{
  if(msg == null) msg = "";
  if(var1 < var2)
  {
    console.log(msg + ", test_greater failed: " + var1 + " < " + var2);
    test_failed = true;
  }
}

// end of unit test framework __________________________________________________

