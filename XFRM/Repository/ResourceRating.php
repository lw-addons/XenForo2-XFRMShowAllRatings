<?php

namespace LiamW\ShowAllRatings\XFRM\Repository;

class ResourceRating extends XFCP_ResourceRating
{
	public function findReviewsInResource(\XFRM\Entity\ResourceItem $resource, array $limits = [])
	{
		$finder = parent::findReviewsInResource($resource, $limits);

		// Unfortunately, finders expose no method to remove a single where condition.
		// We could completely override the method, but that might introduce incompatibilities,
		// so instead have a low execution order, and re-add the existing conditions after resetting them.
		$existingConditions = $finder->getConditions();
		$finder->resetWhere();

		foreach ($existingConditions AS $existingCondition)
		{
			$existingCondition = $this->stripReviewConditionFromStatement($existingCondition);

			if (empty($existingCondition))
			{
				continue;
			}

			$finder->whereSql($existingCondition);
		}

		return $finder;
	}

	public function findLatestReviews(array $viewableCategoryIds = null)
	{
		$finder = parent::findLatestReviews($viewableCategoryIds);

		// Same issue as above :(
		$existingConditions = $finder->getConditions();
		$finder->resetWhere();

		foreach ($existingConditions AS $existingCondition)
		{
			$existingCondition = $this->stripReviewConditionFromStatement($existingCondition);

			if (empty($existingCondition))
			{
				continue;
			}

			$finder->whereSql($existingCondition);
		}

		return $finder;
	}

	protected function stripReviewConditionFromStatement($statement)
	{
		return preg_replace("/(?:\s*(?:AND|OR)\s*)?`?xf_rm_resource_rating`?\.`?is_review`? = '?(?:1|0)'?/i", "", $statement, 1);
	}
}